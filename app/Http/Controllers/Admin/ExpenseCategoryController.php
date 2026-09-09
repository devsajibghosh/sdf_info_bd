<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ExpenseCategory;
use App\Services\FileManager;
use Illuminate\Http\Request;

class ExpenseCategoryController extends Controller
{
    public function list()
    {
        $title             = __('Expense Categories');

        $expenseCategories = ExpenseCategory::searching(['name'])->withSum('expenses', 'amount')->paginate();

        return view('admin.expense_category.list', compact('title', 'expenseCategories'));
    } 

    public function store(Request $request, $id = null)
    {
        $request->validate([
            'name'   => 'required|unique:expense_categories,name,'.$id,
            'status' => 'in:0,1'
        ]);

        $expenseCategory           = !$id ? new ExpenseCategory() : ExpenseCategory::findOrFail($id);
        $expenseCategory->added_by = admin()->id;
        $expenseCategory->name     = $request->name;
        $expenseCategory->status   = $request->status;
        $expenseCategory->save();

        if($request->has('image')) {
            $expenseCategory->image = FileManager::uploadPublic(
                $request->file('image'),
                filePath('expenseCategory'),
                $expenseCategory->image ?? null,
                handleResize('expenseCategory')
            );
            $expenseCategory->save();
        }

        return to_route('admin.expense_category.list')->withSuccess(__('Expense Category Saved'));
    } 

    public function delete($id)
    {
        $expenseCategory = ExpenseCategory::findOrFail($id);
        
        $expenseCategory->delete();

        return to_route('admin.expense_category.list')->withSuccess(__('Expense Category Deleted'));
    }

}
