<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdminLogin;
use App\Models\AdminNotification;
use App\Models\CallLog;
use App\Models\Contact;
use App\Models\Donation;
use App\Models\Expense;
use App\Models\ManualSubmission;
use App\Models\User;
use App\Models\Payment;
use App\Services\SslCommerzChannel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Barryvdh\DomPDF\Facade\Pdf;

class ReportController extends Controller
{
    
    public function accountSummary() 
    {
        goIfUserCan('account-summary');
        
        $title = __('Account Summary');
      
      
$gatewaySums = Payment::join('payment_gateways', 'payments.payment_gateway_id', '=', 'payment_gateways.id')
    ->where('payments.status', 'success')
    ->where('payment_gateways.for_admin', 0)
    ->select(
        DB::raw("CASE 
            WHEN payment_gateways.id IN (1, 47, 48) THEN 'bKash' 
            ELSE payment_gateways.name 
        END as display_name"),
        DB::raw('SUM(payments.amount) as total')
    )
    ->groupBy('display_name') // This merges 1, 47, 48 into one, others stay separate
    ->get();
    
    
    
        $totalDonation1 = $gatewaySums->sum('total'); 
        
        
        
        $totalMBDonation = Payment::success()
            ->select('payment_gateway_id', DB::raw('SUM(amount) as total'))
            ->groupBy('payment_gateway_id')
            ->with('paymentGateway:id,name') 
            ->whereHas('paymentGateway', function ($query) {
                $query->where('for_admin', 0)->where('mobile_banking', 1);
            })->get()->sum('total');
            
        
     
        
        // sdf-taken-loan
        
        $totalExpense = \App\Models\Expense::approved()->sum('amount');
        $sdfTakenLoan = Payment::success()
            ->select(DB::raw('SUM(amount) as total'))
            ->groupBy('payment_gateway_id') 
            ->whereHas('paymentGateway', function ($query) {
                $query->where('key', 'sdf-taken-loan');
            })->first()['total'] ?? 0; 
            
        $bankBalance = Payment::success()
            ->select(DB::raw('SUM(amount) as total'))
            ->groupBy('payment_gateway_id') 
            ->whereHas('paymentGateway', function ($query) {
                $query->where('key', 'bank-balance');
            })->first()['total'] ?? 0; 
            

    
        $cashDonation = Payment::success()
            ->select(DB::raw('SUM(amount) as total'))
            ->groupBy('payment_gateway_id') 
            ->whereHas('paymentGateway', function ($query) {
                $query->where('key', 'cash');
            })->first()['total'] ?? 0; 
            
        
        $totalDonation = $totalDonation1 + $cashDonation;

        // SSLCommerz payment-channel fee breakdown (bKash/Nagad/Rocket/etc. via
        // SSLCommerz = MFS @ 2.5%; VISA/MASTER/AMEX = Card @ 3.5%), derived
        // from the card_brand SSLCommerz already returned per transaction
        // (payments.meta->sslcz_validation) — grouped in SQL so the whole
        // donations/payments table is never pulled into PHP just for this.
        // Only successfully validated SSLCommerz payments are included; a
        // channel that can't be classified (missing/older validation data,
        // e.g. Internet Banking which SDF hasn't assigned a rate to) is kept
        // in the gross total but excluded from the fee calculation rather
        // than guessed.
        $sslGroupSums = Payment::query()
            ->join('payment_gateways', 'payments.payment_gateway_id', '=', 'payment_gateways.id')
            ->where('payments.status', 'success')
            ->where('payment_gateways.key', 'sslcommerz')
            ->select(
                DB::raw(SslCommerzChannel::feeGroupSqlExpression() . ' as fee_group'),
                DB::raw('SUM(payments.amount) as gross')
            )
            ->groupBy('fee_group')
            ->pluck('gross', 'fee_group');

        $sslMfsGross = (float) ($sslGroupSums[SslCommerzChannel::GROUP_MFS] ?? 0);
        $sslCardGross = (float) ($sslGroupSums[SslCommerzChannel::GROUP_CARD] ?? 0);
        $sslUnknownGross = (float) ($sslGroupSums['unknown'] ?? 0);

        $sslMfsFee = round($sslMfsGross * SslCommerzChannel::MFS_FEE_RATE, 2);
        $sslCardFee = round($sslCardGross * SslCommerzChannel::CARD_FEE_RATE, 2);
        $sslCommerzFee = round($sslMfsFee + $sslCardFee, 2);
        $sslCommerzGross = round($sslMfsGross + $sslCardGross + $sslUnknownGross, 2);
        $sslCommerzNet = round($sslCommerzGross - $sslCommerzFee, 2);

        $totalCoCharge = round(($totalMBDonation / 100 * 1.5) + $sslCommerzFee, 2);


        $netDonation = $totalDonation - $totalCoCharge;
        
        $netBalance = ($netDonation + $sdfTakenLoan)  - $totalExpense;
        
        $finalB = $netBalance - $bankBalance;
        
    
        // 5. Pass all data to the view
        return view('admin.report.account_summary', compact(
            'title',
            'finalB',
            'gatewaySums',
            'totalDonation',
            'totalCoCharge',
            'netDonation',
            'totalExpense',
            'sdfTakenLoan',
            'cashDonation',
            'netBalance',
            'bankBalance',
            'sslMfsGross',
            'sslCardGross',
            'sslUnknownGross',
            'sslMfsFee',
            'sslCardFee',
            'sslCommerzFee',
            'sslCommerzGross',
            'sslCommerzNet',
        ));
    }
    
    
// list of 3 months data 

public function reportSummary(Request $request) 
{

$from = $request->get('from', now()->startOfMonth()->toDateString());
$to = $request->get('to', now()->endOfMonth()->toDateString());

    $gatewaySums = Payment::success()
    ->select('payment_gateway_id', DB::raw('SUM(amount) as total'))
    // Add the date filter here
    ->whereBetween('created_at', [
        Carbon::parse($from)->startOfDay(), 
        Carbon::parse($to)->endOfDay()
    ])
    ->whereHas('paymentGateway', function ($query) {
        $query->where('for_admin', 0);
    })
    ->with('paymentGateway:id,name') 
    ->groupBy('payment_gateway_id')
    ->get();  
    
    $cashDonation = Payment::success()
    ->whereBetween('created_at', [$from, $to]) // Apply the time filter here
    ->whereHas('paymentGateway', function ($query) {
        $query->where('key', 'cash');
    })
    ->sum('amount');

    // This calculates the grand total from the collection above
    $totalDonation1 = $gatewaySums->sum('total');
    
    $totalDonation = ($totalDonation1 + $cashDonation);

    $totalGeneralDonation = \App\Models\Donation::where('status', 1)
    ->where('donation_category_id', 1)
    ->whereBetween('created_at', [$from, $to])
    ->sum('amount');

    $memberMonthlyFees = \App\Models\Donation::where('status', 1)
    ->where('donation_category_id', 10)
    ->whereBetween('created_at', [$from, $to])
    ->sum('amount');

    $sdfGoods = \App\Models\Donation::where('status', 1)
    ->where('donation_category_id', 11)
    ->whereBetween('created_at', [$from, $to])
    ->sum('amount');


    $collectedDonation = \App\Models\Donation::where('status', 1)
    ->where('donation_category_id', 9)
    ->whereBetween('created_at', [$from, $to])
    ->sum('amount');


    $totalExpense = Expense::approved()
        ->whereBetween('created_at', [$from, $to])
        ->sum('amount');


    // Prepare a display string for the date column
    $monthDisplay = \Carbon\Carbon::parse($from)->format('M d, Y') . ' - ' . \Carbon\Carbon::parse($to)->format('M d, Y');

    // COMPACT DATA HERE
    return view('admin.report.report', compact(
        'from', 
        'to', 
        'totalDonation', 
        'totalExpense', 
        'monthDisplay',
        'totalGeneralDonation',
        'memberMonthlyFees',
        'sdfGoods',
        'collectedDonation'
    ));

}


// pdf view download


public function reportSummaryPdf(Request $request) 
{
    // ১. Capture Dynamic Dates
    $from = $request->get('from', now()->startOfMonth()->toDateString());
    $to = $request->get('to', now()->endOfMonth()->toDateString());

    // Donation Calculation (আগের মতোই থাকবে)
    $gatewaySums = Payment::success()
        ->select('payment_gateway_id', DB::raw('SUM(amount) as total'))
        ->whereBetween('created_at', [Carbon::parse($from)->startOfDay(), Carbon::parse($to)->endOfDay()])
        ->whereHas('paymentGateway', function ($query) { $query->where('for_admin', 0); })
        ->with('paymentGateway:id,name') 
        ->groupBy('payment_gateway_id')
        ->get();  

    $totalDonation = $gatewaySums->sum('total');

    // Donation Category Helper
    $getSum = function($categoryId) use ($from, $to) {
        return \App\Models\Donation::where('status', 1)
            ->where('donation_category_id', $categoryId)
            ->whereBetween('created_at', [$from, $to])
            ->sum('amount');
    };

    $totalGeneralDonation = $getSum(1);
    $memberMonthlyFees    = $getSum(10);
    $sdfGoods             = $getSum(11);
    $collectedDonation    = $getSum(9);

    // --- EXPENSE CATEGORY LOGIC (Based on your table data) ---
    
    $getExp = function($catId) use ($from, $to) {
        return \App\Models\Expense::approved()
            ->where('expense_category_id', $catId)
            ->whereBetween('created_at', [$from, $to])
            ->sum('amount');
    };

    // আপনার টেবিলের আইডি অনুযায়ী সাজানো:
    $bankExp         = $getExp(8);
    $courierExp      = $getExp(9);
    $salaryExp       = $getExp(10);
    $empOtherExp     = $getExp(11);
    $joinStockExp    = $getExp(13);
    $landExp         = $getExp(15);
    $officialExp     = $getExp(16);
    $printingExp     = $getExp(17);
    $advertisingExp  = $getExp(22);
    $schoolExp       = $getExp(24);
    $loanPayExp      = $getExp(25);
    $transportExp    = $getExp(26);
    $othersExp       = $getExp(27);

    // Total Expense
    $totalExpense = \App\Models\Expense::approved()
        ->whereBetween('created_at', [$from, $to])
        ->sum('amount');

    $monthDisplay = \Carbon\Carbon::parse($from)->format('M d, Y') . ' - ' . \Carbon\Carbon::parse($to)->format('M d, Y');

    // ৩. Generate PDF
    $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('admin.report.pdf_report', compact(
        'from', 'to', 'totalDonation', 'totalExpense', 'monthDisplay',
        'totalGeneralDonation', 'memberMonthlyFees', 'sdfGoods', 'collectedDonation',
        'bankExp', 'courierExp', 'salaryExp', 'empOtherExp', 'joinStockExp', 
        'landExp', 'officialExp', 'printingExp', 'advertisingExp', 
        'schoolExp', 'loanPayExp', 'transportExp', 'othersExp'
    ))->setPaper('A4', 'portrait');

    return $pdf->download('finance-report-'.$from.'.pdf');
}
    
    
    
    
    // delete contact submission
    
    public function deleteContactSubmission($id) 
    {
        goIfUserCan('contact_report');
        $contact = Contact::findOrFail($id);
        $contact->delete();
        
        return back()->withSuccess(__('Contact deleted'));
    }
    
    public function usersByArea(Request $request)
    {
        goIfUserCan('users-by-area');
        $title = __('Member List by Area');
    
        // Get a flat list of unique divisions for the first dropdown
        $divisions = User::whereNotNull('division')->distinct()->pluck('division');
    
        // --- NEW: Create a structured array of all locations ---
        $locations = [];
        $allUsersLocations = User::select('division', 'district', 'upazila')
                                ->whereNotNull('division')
                                ->whereNotNull('district')
                                ->whereNotNull('upazila')
                                ->distinct()
                                ->get();
    
        foreach ($allUsersLocations as $user) {
            $locations[$user->division][$user->district][] = $user->upazila;
        }
        // Ensure nested upazila arrays are unique
        foreach ($locations as $division => &$districts) {
            foreach ($districts as $district => &$upazilas) {
                $upazilas = array_values(array_unique($upazilas));
            }
        }
        // --- End of New Section ---
    
        // Your existing query logic remains the same
        $query = User::query();
    
        if ($request->filled('division')) {
            $query->where('division', $request->division);
        }
        if ($request->filled('district')) {
            $query->where('district', $request->district);
        }
        if ($request->filled('upazila')) {
            $query->where('upazila', $request->upazila);
        }
    
        $widget = [
            'count' => $query->count()
        ];
        
        $members = $query->latest()->paginate(20);
        
        // Pass the new '$locations' variable to the view
        return view('admin.report.user_by_area', compact(
            'title', 
            'members', 
            'divisions', // Still needed for the initial division dropdown
            'locations', // The new structured data for JavaScript
            'widget'
        ));
    }
    
    public function contactSubmissions()
    {
        $title = 'Contact Submissions';

        $contacts = Contact::searching(['name', 'phone_number'])->latest()->paginate();

        return view('admin.report.contact_submissions', compact('contacts'));
    }

    public function callLogs()
    {
        goIfUserCan('call-log-report');
        $title = __('Call Logs');

        $callLogs = CallLog::with(['admin', 'user'])->latest()->searching(['admin:email', 'note', 'amount', 'admin:name', 'user:email', 'user:phone_number']);

        if (admin()->id != 1) {
            $callLogs = $callLogs->where('admin_id', admin()->id);
        }

        $callLogs = $callLogs->paginate();

        return view('admin.report.call_logs', compact('title', 'callLogs'));
    }


    public function adminLogins()
    {
        goIfUserCan('admin-log-report');
        $title = 'Admin Logins';

        $adminLogins = AdminLogin::latest()->searching(['admin:email', 'browser', 'ip', 'city', 'device_type'])->paginate();

        return view('admin.report.admin_logins', compact('title', 'adminLogins'));
    }

    public function notifications()
    {
        goIfUserCan('notification-report');
        $title = 'Notifications';

        $notifications = AdminNotification::latest()->paginate();

        return view('admin.report.notifications', compact('title', 'notifications'));
    }
    
    public function deleteAllNotification(Request $request)
    {
        goIfUserCan('notification-report');
        AdminNotification::truncate();
        return back()->withSuccess(__('Notifications deleted successfully'));
    }

    public function markAllAsRead()
    {
        goIfUserCan('notification-report');
        AdminNotification::unRead()->update(['is_read' => 1]);
        return back()->withSuccess(__('Notifications marked as read'));
    }

    public function deleteNotification($id)
    {
        goIfUserCan('notification-report');
        $notification = AdminNotification::findOrFail($id);

        $notification->delete();

        return back()->withSuccess(__('Notification deleted successfully'));
    }

    public function notificationRead($id)
    {
        goIfUserCan('notification-report');
        $notification = AdminNotification::findOrFail($id);

        $notification->is_read = 1;
        $notification->save();

        if ($notification->link) {
            return redirect($notification->link);
        }

        return back();
    }
}
