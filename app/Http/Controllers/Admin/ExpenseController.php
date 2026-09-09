<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use App\Models\Expense;
use App\Models\ExpenseCategory;
use App\Services\FileManager;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;


class ExpenseController extends Controller
{
    public function uploadCsv(Request $request)
    {
        goIfUserCan('expense-list');
        $request->validate([
            'file' => 'required|file|mimes:csv,txt|max:2048',
        ]);
    
        // Read CSV into memory
        $rows = [];
        if (($handle = fopen($request->file('file')->getRealPath(), 'r')) !== false) {
            while (($data = fgetcsv($handle, 1000, ',')) !== false) {
                $rows[] = $data;
            }
            fclose($handle);
        }
    
        unset($rows[0]); // remove header row
    
        // Collect unique categories
        $categories = [];
        foreach ($rows as $row) {
            if (count($row) < 3) {
                continue;
            }

            $categories[] = trim($row[1]); // category name column
        }
        $categories = array_unique($categories);
    
        DB::beginTransaction();
        try {
            // Fetch existing categories
            $existingCategories = ExpenseCategory::whereIn('name', $categories)->get()->keyBy('name');
    
            // Create missing categories
            foreach ($categories as $cat) {
                if (!isset($existingCategories[$cat])) {
                    $existingCategories[$cat] = ExpenseCategory::create([
                        'added_by' => admin()->id,
                        'status'   => 1, 
                        'name'     => $cat,
                    ]);
                }
            }
    
            // Loop through rows and insert expenses
            foreach ($rows as $row) {
                if (count($row) < 3) {
                    continue;
                }

                $time     = $row[0]; // created_at
                $category = trim($row[1]);
                $amount   = $row[2];
                $note     = $row[3] ?? null;
                $status   = isset($row[4]) && $row[4] == 1 ? 1 : 0; // optional approve column
    
                $expense = new Expense();
                $expense->expense_category_id = $existingCategories[$category]->id;
                $expense->amount              = $amount;
                $expense->note                = $note;
                $expense->expense_by          = admin()->id;
                $expense->status              = $status;
    
                if ($status) {
                    $expense->approved_by = admin()->id;
                }
    
                if (!empty($time)) {
                    $expense->created_at = $time;
                }
    
                $expense->save();
            }
    
            DB::commit();
            return back()->withSuccess(__('Expenses imported successfully'));
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withError(__($e->getMessage()));
        }
    }

    
    public function approve(Request $request, $id)
    {
        goIfUserCan('expense-approve');

        $expense = Expense::notApproved()->findOrFail($id);

        $expense->status = 1;

        $expense->approved_by = admin()->id;

        $expense->save();

        return back()->withSuccess(__('Expense Approved Successfully'));
    }

    public function unapprove($id)
    {
        goIfUserCan('expense-unapprove');

        $expense = Expense::approved()->findOrFail($id);

        $expense->status = 0;

        $expense->save();

        return back()->withSuccess(__('Expense Unapproved Successfully'));
    }
    
    
    
public function list(Request $request)
{
    goIfUserCan('expense-list');
    $title = __('Expenses');

    // Query Build
    $query = Expense::with('category', 'expenseBy', 'expenseFor', 'approvedBy')
        ->searching(['note', 'amount'])
        ->latest();

    // Export Logic (PDF or Excel)
    if ($request->has('download') || $request->has('export_excel')) {
        goIfUserCan('download-expense');
        
        $expenses = $query->get();

        // PDF Download
        if ($request->boolean('download')) {
            $pdf = Pdf::loadView('admin.expenses.pdf', [
                'title' => $title,
                'expenses' => $expenses,
                'expenseCategories' => ExpenseCategory::active()->get(),
                'members' => Admin::get()
            ]);
            return $pdf->download('expenses_report.pdf');
        }

        // Excel (CSV) Download
        if ($request->boolean('export_excel')) {
            $fileName = 'expenses_report.csv';
            $headers = [
                "Content-type"        => "text/csv; charset=utf-8",
                "Content-Disposition" => "attachment; filename=$fileName",
                "Pragma"              => "no-cache",
                "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
                "Expires"             => "0"
            ];

            $callback = function() use ($expenses) {
                $file = fopen('php://output', 'w');
                // UTF-8 BOM for Excel to show characters correctly
                fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));
                
                // Headings (Ensure these match your PDF headers)
                fputcsv($file, ['Date', 'Category', 'Note', 'Amount', 'Status', 'Expense By']);

                foreach ($expenses as $expense) {
                    fputcsv($file, [
                        $expense->created_at->format('Y-m-d H:i'),
                        $expense->category->name ?? __('N/A'),
                        $expense->note,
                        $expense->amount,
                        $expense->status ? __('Approved') : __('Pending'),
                        $expense->expenseBy->name ?? __('N/A')
                    ]);
                }
                fclose($file);
            };

            return response()->stream($callback, 200, $headers);
        }
    }

    // Default View
    $expenses = $query->paginate(15);
    $expenseCategories = ExpenseCategory::active()->get();
    $members = Admin::get();

    return view('admin.expenses.list', compact('title', 'expenseCategories', 'members', 'expenses'));
}
    
    
    public function approved(Request $request)
    {
        goIfUserCan('expense-list');
        $title = __('Approved Expenses');

        $expenses = Expense::with('category', 'expenseBy', 'expenseFor', 'approvedBy')
            ->approved()
            ->searching(['note', 'amount'])
            ->latest();

        $expenseCategories = ExpenseCategory::active()->get();
        $members = Admin::get();

        if ($request->has('download') && $request->boolean('download')) {
            goIfUserCan('download-expense');

            $expenses = $expenses->get();

            $pdf = Pdf::loadView('admin.expenses.pdf', compact('title', 'expenses', 'expenseCategories', 'members'));
            return $pdf->download('expenses_report.pdf');
        }

        $expenses = $expenses->paginate(15);

        return view('admin.expenses.list', compact('title', 'expenseCategories', 'members', 'expenses'));
    }

    public function unapproved(Request $request)
    {
        goIfUserCan('expense-list');
        $title = __('Un Approved Expenses');

        $expenses = Expense::with('category', 'expenseBy', 'expenseFor', 'approvedBy')
            ->notApproved()
            ->searching(['note', 'amount'])
            ->latest();

        $expenseCategories = ExpenseCategory::active()->get();
        $members = Admin::get();

        if ($request->has('download') && $request->boolean('download')) {
            goIfUserCan('download-expense');

            $expenses = $expenses->get();

            $pdf = Pdf::loadView('admin.expenses.pdf', compact('title', 'expenses', 'expenseCategories', 'members'));
            return $pdf->download('expenses_report.pdf');
        }

        $expenses = $expenses->paginate(15);

        return view('admin.expenses.list', compact('title', 'expenseCategories', 'members', 'expenses'));
    }

    public function store(Request $request, $id = null)
    {
        goIfUserCan('expense-list');

        // upload_max_filesize/post_max_size are PHP_INI_PERDIR: they're locked in
        // before this request even reaches userland PHP, so ini_set() here can
        // never raise them (the old ini_set() calls that used to sit here were a
        // no-op). The real ceiling is set in php.ini; see FileManager for the
        // actual image-processing memory safety net, which IS effective at
        // runtime. A large upload still needs a bit more time than the default
        // 30s to transfer/process, so bump execution time only (harmless, and
        // PHP_INI_ALL).
        set_time_limit(120);

        $request->validate([
            'expense_category_id' => 'required|exists:expense_categories,id',
            'amount'              => 'required|numeric|gt:0',
            'note'                => 'nullable|string|max:1000',
            'image'               => [
                'nullable',
                'image',
                'mimes:jpg,jpeg,png,webp',
                'max:30720', // 30MB raw upload; FileManager auto-optimizes it down
                'dimensions:max_width=8000,max_height=8000',
            ],
            'created_at'          => 'nullable|date'
        ]);

        $expense = $id ? Expense::findOrFail($id) : new Expense();

        $expense->expense_category_id = $request->expense_category_id;
        $expense->amount              = $request->amount;
        $expense->note                = $request->note;
        $expense->expense_by          = admin()->id;

        if ($request->created_at) {
            $expense->created_at = $request->created_at;
        }

        if ($request->hasFile('image')) {
            $expense->image = FileManager::uploadPublic(
                $request->file('image'),
                filePath('expense'),
                $expense->image ?? null,
                handleResize('expense')
            );
        }

        $expense->save();

        return to_route('admin.expense.list')->withSuccess(__('Expense Saved Successfully'));
    }

    public function delete($id)
    {
        goIfUserCan('expense-list');
        $expense = Expense::findOrFail($id);
        $expense->delete();

        return to_route('admin.expense.list')->withSuccess(__('Expense Deleted Successfully'));
    }
}
