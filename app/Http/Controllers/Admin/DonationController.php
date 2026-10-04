<?php

namespace App\Http\Controllers\Admin;

use App\Facades\System;
use App\Http\Controllers\Controller;
use App\Models\Donation;
use App\Models\DonationCategory;
use App\Models\Donor;
use App\Models\Payment;
use App\Models\PaymentGateway;
use App\Models\User;
use App\Models\ManualSubmission;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;


class DonationController extends Controller
{
    
    
// donations pdf download monthwise



public function downloadMonthlyPdf()
{
goIfUserCan('view-donation');

$donations = Donation::with(['user', 'donor', 'payment.paymentGateway', 'category', 'approvedBy'])
    ->where('created_at', '>=', now()->subDays(2))
    ->latest()
    ->get();

    $pdf = Pdf::loadView('admin.donation.recipet', compact('donations'))
        ->setPaper('A4', 'landscape');

    $fileName = 'SDF_Donations_' . now()->subDays(2)->format('d_M') . '_to_' . now()->format('d_M_Y') . '.pdf';
    return $pdf->download($fileName);
}


public function downloadMonthlyExcel()
    {
        goIfUserCan('view-donation');

        // Fetch ALL donations without any date restriction
        $donations = Donation::with(['user', 'donor', 'payment.paymentGateway', 'category', 'approvedBy'])
            ->latest()
            ->get();

        $fileName = 'SDF_All_Donations_' . now()->format('d_M_Y') . '.xls';

        // Headers for native Excel download
        header("Content-Type: application/vnd.ms-excel; charset=utf-8");
        header("Content-Disposition: attachment; filename=\"{$fileName}\"");
        header("Cache-Control: max-age=0");
        
        if (ob_get_level()) {
            ob_end_clean();
        }

        echo '<html xmlns:o="urn:schemas-microsoft-com:office:office" xmlns:x="urn:schemas-microsoft-com:office:excel" xmlns="http://www.w3.org/TR/REC-html40">';
        echo '<head><meta charset="utf-8"></head>';
        echo '<body>';
        echo '<table border="1">';
        
        // Report Title Row
        echo '<tr><th colspan="8" style="font-size: 16px; color: #F54927; text-align: center;">Sanatani Development Foundation - All Donations Report</th></tr>';
        
        // Table Headers
        echo '<tr style="background-color: #f2f2f2; font-weight: bold;">';
        echo '<th>Name</th>';
        echo '<th>Phone</th>';
        echo '<th>Transaction No</th>';
        echo '<th>Channel</th>';
        echo '<th>Amount</th>';
        echo '<th>Category</th>';
        echo '<th>Date</th>';
        echo '<th>Approved By</th>';
        echo '</tr>';

        // Table Rows
        foreach ($donations as $donation) {
            $name = $donation->user?->name ?? $donation->donor?->name ?? '-';
            
            // Full phone number with text format to prevent leading zero loss
            $phone = $donation->phone_number ?? 'N/A';
            
            $transactionNo = $donation->payment?->transaction_no ?? '-';
            $channel = $donation->payment?->paymentGateway?->name ?? '-';
            $amount = number_format($donation->amount, 2, '.', '');
            $category = $donation->category?->name ?? '-';
            $date = $donation->created_at->format('d M Y');
            $approvedBy = $donation->approvedBy?->name ?? '-';

            echo '<tr>';
            echo '<td>' . htmlspecialchars($name) . '</td>';
            echo '<td style="mso-number-format:\@;">' . htmlspecialchars($phone) . '</td>';
            echo '<td style="mso-number-format:\@;">' . htmlspecialchars($transactionNo) . '</td>';
            echo '<td>' . htmlspecialchars($channel) . '</td>';
            echo '<td style="mso-number-format:\#,##0.00;">' . $amount . '</td>';
            echo '<td>' . htmlspecialchars($category) . '</td>';
            echo '<td>' . htmlspecialchars($date) . '</td>';
            echo '<td>' . htmlspecialchars($approvedBy) . '</td>';
            echo '</tr>';
        }

        echo '</table>';
        echo '</body>';
        echo '</html>';
        exit;
    }


    
    
    public function deleteManualSubmission($id)
    {
        goIfUserCan('manual-submission');
        $submission = ManualSubmission::findOrFail($id);
        
        $payment = $submission?->payment ?? null;
        if($payment) {
             $payment->delete();
        }
        
        $submission->delete();
        
        return back()->withSuccess(__('Data deleted succesfully'));
    }
    
    public function manualSubmissionsSave(Request $request, $id = null)
    {
        goIfUserCan('manual-submission');
        $request->validate([
            'gateway_id' => 'required|integer|exists:payment_gateways,id',
            'created_at' => 'required|date',
            'amount'     => 'required|numeric|gt:0'
        ]);
         
        
        $paymentGateway = PaymentGateway::findOrFail($request->gateway_id);
        
        $submission = $id ? ManualSubmission::find($id) : new ManualSubmission();
    
        $payment = ($submission->exists && $submission->payment_id) 
                    ? Payment::findOrNew($submission->payment_id) 
                    : new Payment();
    
        $payment->amount             = $request->amount;
        $payment->payment_gateway_id = $request->gateway_id;
        $payment->created_at         = $request->created_at; // Also update the payment date
        $payment->method             = $paymentGateway->key;
        
        if (!$payment->exists) {
            $payment->status         = 'success';
            $payment->currency       = 'BDT';    
            $payment->transaction_no = 'MANUAL-' . strtoupper(Str::random(10)); 
            $payment->user_id        = 0;  
            $payment->donor_id       = 0;  
        }
        
        $payment->save(); 
         
        $submission->created_at   = $request->created_at;
        $submission->amount       = $request->amount;
        $submission->note       = $request->note;
        $submission->gateway_id   = $request->gateway_id;
        $submission->payment_id   = $payment->id;  
        $submission->save();
        
        
        $message = $id ? __('Data updated successfully') : __('Data saved successfully');
        
        return back()->with('success', $message); 
    }
    
    public function manualSubmissions(){
        goIfUserCan('manual-submission');
        $title = __("Manual Submissions");
        $manualSubmissions = ManualSubmission::latest()->paginate();
        $gateways = PaymentGateway::where('for_admin', 1)->get();
        
        return view('admin.donation.manual_submissions', compact('title', 'manualSubmissions','gateways'));
    }
    
    public function manualForm()
    {
        goIfUserCan('manual-donation');
        $title = __('Manual donation create');

        $categories = DonationCategory::active()->get();

        $paymentGateways = PaymentGateway::active()->get();

        return view('admin.donation.form', compact('title', 'categories', 'paymentGateways'));
    }

    public function uploadCsv(Request $request)
    {
        goIfUserCan('manual-donation');
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
    
        // Collect unique values
        $categories = [];
        $gateways   = [];
        $phones     = [];
    
        foreach ($rows as $row) {
            if (count($row) < 7) {
                continue;
            }

            $categories[] = $row[2];
            $gateways[]   = strtolower($row[1]);
    
            // ✅ normalize phone here
            $phone = trim($row[3]);
            if (!str_starts_with($phone, '0')) {
                $phone = '0' . $phone;
            }
            $phones[] = $phone;
        }
    
        $categories = array_unique($categories);
        $gateways   = array_unique($gateways);
        $phones     = array_unique($phones);
    
        DB::beginTransaction();
        try {
            // Fetch existing categories, gateways, donors
            $existingCategories = DonationCategory::whereIn('name', $categories)->get()->keyBy('name');
            $existingGateways   = PaymentGateway::whereIn('key', $gateways)->where('manual', 1)->get()->keyBy('key');
            $existingDonors     = Donor::whereIn('phone_number', $phones)->get()->keyBy('phone_number');
    
            // Create missing categories
            foreach ($categories as $cat) {
                if (!isset($existingCategories[$cat])) {
                    $existingCategories[$cat] = DonationCategory::create([
                        'admin_id' => admin()->id,
                        'status'   => 1,
                        'details'  => "Created while bulk import of donations were made",
                        'name'     => $cat,
                    ]);
                }
            }
    
            // Create missing gateways
            foreach ($gateways as $gw) {
                if (!isset($existingGateways[$gw])) {
                    $existingGateways[$gw] = PaymentGateway::create([
                        'status' => 1,
                        'manual' => 1,
                        'name'   => ucfirst($gw),
                        'key'    => $gw,
                    ]);
                }
            }
    
            // Create missing donors
            foreach ($phones as $phone) {
                if (!isset($existingDonors[$phone])) {
                    $existingDonors[$phone] = Donor::create([
                        'phone_number' => $phone,
                    ]);
                }
            }
    
            // Loop through rows and insert payments + donations
            foreach ($rows as $row) {
                if (count($row) < 7) {
                    continue;
                }

                $time            = $row[0];
                $gatewayKey      = strtolower($row[1]);
                $category        = $row[2];
                $phone           = trim($row[3]);
                $trx             = $row[4];
                $amount          = $row[5];
                $fieldCollection = intval($row[6] ?? 0);
    
                // ✅ normalize again just in case
                if (!str_starts_with($phone, '0')) {
                    $phone = '0' . $phone;
                }
    
                $donorId    = $existingDonors[$phone]->id ?? 0;
                $gateway    = $existingGateways[$gatewayKey];
                $categoryId = $existingCategories[$category]->id;
    
                // Create payment
                $payment = Payment::create([
                    'donor_id'           => $donorId,
                    'status'             => 'success',
                    'amount'             => $amount,
                    'currency'           => generalSetting('currency'),
                    'transaction_no'     => $trx,
                    'payment_gateway_id' => $gateway->id,
                    'method'             => $gatewayKey,
                ]);
    
                // Create donation
                Donation::create([
                    'created_at'           => $time,
                    'phone_number'         => $phone,
                    'donor_id'             => $donorId,
                    'amount'               => $amount,
                    'donation_category_id' => $categoryId,
                    'payment_id'           => $payment->id,
                    'status'               => 1,
                    'is_manual'            => 1,
                    'field_collection'     => $fieldCollection,
                    'approved_by'          => admin()->id,
                    'admin_id'             => admin()->id,
                ]);
            }
    
            DB::commit();
            return back()->withSuccess(__('Donations imported'));
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Donation CSV import failed: ' . $e->getMessage());
            return back()->withError(__('Import failed. Please check the file and try again.'));
        }
    }


    public function manualFormSubmit(Request $request, $id = null)
    {
        goIfUserCan('manual-donation');
        $request->validate([
            'date'                 => 'required|date',
            'name'                 => 'nullable',
            'phone_number'         => 'required',
            'amount'               => 'numeric|gt:0',
            'field_collection'     => 'required|in:0,1',
            'donation_category_id' => 'required',
            'payment_gateway_id'   => 'required'
        ]);
    
        $paymentGateway = PaymentGateway::findOrFail($request->payment_gateway_id);

        $donation = null;

        DB::transaction(function () use ($request, $paymentGateway, &$donation) {
            $user  = User::where('phone_number', $request->phone_number)->first();
            $donor = Donor::where('phone_number', $request->phone_number)->first();

            if (!$donor) {
                $donor               = new Donor();
                $donor->phone_number = $request->phone_number;
                $donor->name         = $request->name;
                $donor->created_at   = $request->date; // set created_at from form date
                $donor->save();
            }

            $payment                     = new Payment();
            $payment->user_id            = @$user->id ?? 0;
            $payment->donor_id           = @$donor->id ?? 0;
            $payment->status             = 'success';
            $payment->amount             = $request->amount;
            $payment->currency           = generalSetting('currency');
            $payment->transaction_no     = generateTransactionId();
            $payment->payment_gateway_id = $request->payment_gateway_id;
            $payment->method             = $paymentGateway->key;
            $payment->created_at         = $request->date; // set created_at from form date
            $payment->save();

            $donation                       = new Donation();
            $donation->phone_number         = $request->phone_number;
            $donation->user_id              = @$user->id ?? 0;
            $donation->donor_id             = @$donor->id ?? 0;
            $donation->payment_id           = $payment->id;
            $donation->amount               = $request->amount;
            $donation->donation_category_id = $request->donation_category_id;
            $donation->status               = 1;
            $donation->is_manual            = 1;
            $donation->field_collection     = $request->field_collection;
            $donation->approved_by          = admin()->id;
            $donation->admin_id             = admin()->id;
            $donation->created_at           = $request->date; // set created_at from form date
            $donation->save();
        });

        $donation->load(['user', 'donor']);

        $recipient = $donation->user ?: $donation->donor;

        (new \App\Services\DonationSmsService())->sendSuccessSms(
            $donation->phone_number ?: $recipient?->phone_number,
            (float) $donation->amount,
            $recipient,
            $donation->id,
            'donation_manual',
            $donation->payment?->transaction_no
        );

        return to_route('admin.donation.list')->withSuccess(__('Donation successfully saved'));
    }



public function list()
{
    goIfUserCan('view-donation');

    $title = __('Donations');
    $search = trim(request('search', ''));

    // যদি সার্চ বক্সে মাস্কড নম্বর থাকে (যেমন: 0131xxxx053)
    if ($search && preg_match('/^(\d{4})[xX\*]+(\d{3})$/', $search, $matches)) {
        
        $firstPart = $matches[1]; // 0131
        $lastPart  = $matches[2]; // 053

        // Donation এবং সংশ্লিষ্ট User টেবিলের phone_number চেক করা হচ্ছে
        $donations = Donation::where(function ($query) use ($firstPart, $lastPart) {
            $query->where('phone_number', 'like', $firstPart . '%' . $lastPart)
                  ->orWhereHas('user', function ($q) use ($firstPart, $lastPart) {
                      $q->where('phone_number', 'like', $firstPart . '%' . $lastPart);
                  });
        });

    } else {
        // সাধারণ সার্চ লজিক
        $donations = Donation::searching([
            'category:name',
            'email',
            'phone_number',
            'user:name',
            'user:email',
            'user:phone_number',
            'payment:transaction_no'
        ]);
    }

    // The main Donations list should only show attempts a donor could actually
    // act on (pending) or completed (approved). SSLCommerz callbacks create the
    // Donation/Payment rows before redirecting to the gateway (needed for the
    // round-trip), so every cancelled/failed checkout otherwise shows up here
    // as noise. Those rows are kept (not deleted) for support/audit purposes —
    // they remain visible under the existing "Rejected Donations" filter.
    $donations = $donations->with('payment.paymentGateway')->where('status', '!=', 2)->latest()->paginate();

    return view('admin.donation.list', compact('title', 'donations'));
}



    public function approved()
    {
        goIfUserCan('view-donation');

        $title = __('Approved Donations');

        $donations = Donation::searching(['category:name', 'email', 'phone_number', 'user:name', 'user:email', 'user:phone_number', 'payment:transaction_no'])->with('payment.paymentGateway')->success()->latest()->paginate();

        return view('admin.donation.list', compact('title', 'donations'));
    }
    
    
    

    public function rejected()
    {
        goIfUserCan('view-donation');

        $title = __('Rejected Donations');

        $donations = Donation::searching(['category:name', 'email', 'phone_number', 'user:name', 'user:email', 'user:phone_number', 'payment:transaction_no'])->with('payment.paymentGateway')->rejected()->latest()->paginate();

        return view('admin.donation.list', compact('title', 'donations'));
    }


    public function pending()
    {
        goIfUserCan('view-donation');

        $title = __('Pending Donations');

        $donations = Donation::searching(['category:name', 'email', 'phone_number', 'user:name', 'user:email', 'user:phone_number', 'payment:transaction_no'])->with('payment.paymentGateway')->pending()->latest()->paginate();

        return view('admin.donation.list', compact('title', 'donations'));
    }
    
    
    

 public function delete($id)
{
    goIfUserCan('delete-donation');

    $donation = Donation::findOrFail($id);

            \App\Models\Payment::where('id', $donation->payment_id)
            ->update(['amount' => 0]);

    // ADD THIS LINE TO ACTUALLY DELETE
    $donation->delete(); 

    return redirect()
        ->route('admin.donation.list', ['page' => request()->get('page', 1)])
        ->withSuccess(__('Donation deleted successfully'));
}
    
    
    
    
    
    
    // approve donation -admin
    
    
public function autoApproveApi(Request $request)
    {
        // ১. সিকিউরিটি চেক
        if (!hash_equals((string) config('services.sms_automation_token'), (string) $request->header('X-Auth-Token'))) {
            return response()->json(['status' => 'unauthorized'], 401);
        }

        $message = $request->input('message');
        // 'from' ফিল্ড থেকে আসা নম্বরটিকে ১১ ডিজিটে ফরম্যাট করা (যেমন: +88017... থেকে 017...)
        $fromNumber = $this->formatPhoneNumber($request->input('from')); 

        if (!$message || !$fromNumber) {
            return response()->json(['status' => 'error', 'message' => 'Invalid data'], 400);
        }

        // ২. Regex দিয়ে এসএমএস থেকে অ্যামাউন্ট বের করা
        preg_match('/(?:Amount|Tk|Amount\sTk)\.?\s*:?\s*([\d,.]+)/i', $message, $amountMatches);

        if (isset($amountMatches[1])) {
            $smsAmount = (float) str_replace(',', '', $amountMatches[1]);

            // ৩. লজিক: ১১ ডিজিটের ফোন নম্বর এবং ডেসিমেল অ্যামাউন্ট দিয়ে পেন্ডিং ডোনেশন খোঁজা
            $donation = Donation::where('status', 0) // পেন্ডিং
                ->where(function($query) use ($smsAmount) {
                    // DB এর 1200.00000000 এর সাথে SMS এর 1200 ম্যাচ করার জন্য
                    $query->whereRaw('CAST(amount AS DECIMAL(10,2)) = ?', [$smsAmount]);
                })
                ->where(function($query) use ($fromNumber) {
                    // সরাসরি ১১ ডিজিট নম্বর ম্যাচিং
                    $query->where('phone_number', $fromNumber)
                          ->orWhereHas('user', function($q) use ($fromNumber) {
                              $q->where('phone_number', $fromNumber);
                          })
                          ->orWhereHas('donor', function($q) use ($fromNumber) {
                              $q->where('phone_number', $fromNumber);
                          });
                })
                ->first();

            if ($donation) {
                $systemAdminId = 1; 
                $this->processFinalApproval($donation, $systemAdminId);

                return response()->json([
                    'status' => 'success', 
                    'message' => "Auto approved for $fromNumber with amount $smsAmount"
                ]);
            }

            Log::warning("Sms Automation: No match for Phone: $fromNumber, Amount: $smsAmount");
        }

        return response()->json(['status' => 'ignored', 'message' => 'No matching pending record']);
    }

    /**
     * কমন লজিক: এসএমএস পাঠানো এবং ডাটাবেস আপডেট (অটো ও ম্যানুয়াল উভয়ের জন্য)
     */
    private function processFinalApproval($donation, $approvedById)
    {
        DB::transaction(function () use ($donation, $approvedById) {

            // ফোন নম্বর বের করা
            $phone = $donation->phone_number ?? ($donation->user->phone_number ?? ($donation->donor->phone_number ?? null));

            // একই SMS logic যা SSLCommerz payment-success flow ব্যবহার করে (GatewayHelper::sendPaymentSuccessSms)
            // reuse করা হচ্ছে, যাতে message format ও donor-name resolution দুই জায়গায় আলাদা না হয়ে যায়।
            $recipient = $donation->user ?: $donation->donor;

            (new \App\Services\DonationSmsService())->sendSuccessSms(
                $phone,
                (float) $donation->amount,
                $recipient,
                $donation->id,
                'donation_approved',
                $donation->payment?->transaction_no
            );

            // ৪. ডোনেশন আপডেট
            $donation->update([
                'approved_by' => $approvedById,
                'status'      => 1, // Approved
                'updated_at'  => now(),
            ]);

            // ৫. পেমেন্ট স্ট্যাটাস আপডেট
            if ($donation->payment) {
                $donation->payment->update(['status' => 'success']);
            }
        });
    }

    /**
     * ম্যানুয়াল অ্যাপ্রুভ (অ্যাডমিন প্যানেল)
     */
    public function approve($id)
    {
        goIfUserCan('delete-donation');

        $donation = Donation::pending()->with(['user', 'donor', 'payment'])->findOrFail($id);
        
        $this->processFinalApproval($donation, admin()->id);

        return redirect()
            ->route('admin.donation.list', ['page' => request()->get('page', 1)])
            ->withSuccess(__('Donation approved successfully'));
    }

    /**
     * ১১ ডিজিট ফরম্যাটিং হেল্পার
     */
    private function formatPhoneNumber($number)
    {
        // সব নন-ডিজিট ক্যারেক্টার মুছে ফেলা
        $number = preg_replace('/[^0-9]/', '', $number);
        
        // যদি নম্বরটি ১১ ডিজিটের বেশি হয় (যেমন ৮৮০১৭...), তবে শেষ ১১ ডিজিট নেওয়া
        if (strlen($number) > 11) {
            $number = substr($number, -11);
        }
        
        return $number;
    }

    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    // reject donation

    public function reject(Request $request, $id)
    {
        goIfUserCan('delete-donation');

        $donation = Donation::pending()->findOrFail($id);
        $donation->status = 2;
        $donation->save();

        return redirect()
            ->route('admin.donation.list', ['page' => $request->get('page', 1)])
            ->withSuccess(__('Donation rejected successfully'));

    }
}
