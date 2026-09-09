<?php

namespace App\Http\Controllers\Admin;

use App\Facades\System;
use App\Helpers\BulkSmsHelper;
use App\Http\Controllers\Controller;
use App\Models\Donation;
use App\Models\DonationCategory;
use App\Models\Donor;
use App\Models\Payment;
use App\Models\PaymentGateway;
use App\Models\User;
use Illuminate\Http\Request;

class DonationController extends Controller
{
    public function manualForm()
    {
        $title = __('Manual donation create');

        $categories = DonationCategory::active()->get();

        $paymentGateways = PaymentGateway::active()->get(); 

        return view('admin.donation.form', compact('title', 'categories', 'paymentGateways'));
    }
    
    public function manualFormSubmit(Request $request, $id = null) {
        $request->validate([
            'name'                 => 'nullable',
            'phone_number'         => 'required',
            'amount'               => 'numeric|gt:0',
            'donation_category_id' => 'required',
            'payment_gateway_id'   => 'required'
        ]);

        $paymentGateway = PaymentGateway::findOrFail($request->payment_gateway_id);

        $user  = User::where('phone_number', $request->phone_number)->first();
        $donor = Donor::where('phone_number', $request->phone_number)->first();

        
        if(!$user && !$donor) {
            $donor               = new Donor();
            $donor->phone_number = $request->phone_number;
            $donor->name         = $request->name;
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
        $donation->admin_id             = admin()->id;
        $donation->save();

        return to_route('admin.donation.list')->withSuccess(__('Donation successfully saved'));
    }
    
    public function list()
    {
        goIfUserCan('view-donation');
        
        $title = __('Donations');

        $donations = Donation::searching(['category:name', 'email', 'phone_number', 'user:name', 'user:email', 'user:phone_number', 'payment:transaction_no'])->latest()->paginate();

        return view('admin.donation.list', compact('title', 'donations'));
    }

    public function approved() {
        goIfUserCan('view-donation');
        
        $title = __('Approved Donations');

        $donations = Donation::searching(['category:name', 'email', 'phone_number', 'user:name', 'user:email', 'user:phone_number', 'payment:transaction_no'])->success()->latest()->paginate();

        return view('admin.donation.list', compact('title', 'donations'));
    }

    public function rejected() {
        goIfUserCan('view-donation');
        
        $title = __('Rejected Donations');

        $donations = Donation::searching(['category:name', 'email', 'phone_number', 'user:name', 'user:email', 'user:phone_number', 'payment:transaction_no'])->rejected()->latest()->paginate();

        return view('admin.donation.list', compact('title', 'donations'));
    }

    
    public function pending() {
        goIfUserCan('view-donation');
        
        $title = __('Pending Donations');

        $donations = Donation::searching(['category:name', 'email', 'phone_number', 'user:name', 'user:email', 'user:phone_number', 'payment:transaction_no'])->pending()->latest()->paginate();

        return view('admin.donation.list', compact('title', 'donations'));
    }

    public function delete($id)
    {
        goIfUserCan('delete-donation');
        
        $donation = Donation::findOrFail($id);

        $donation->delete();

        return back()->withSuccess(__('Donation deleted successfully'));
    }

    public function approve($id)
    {
        goIfUserCan('delete-donation');
        
        $donation = Donation::pending()->with(['user', 'donor'])->findOrFail($id);

        if($donation->user) {
            (new BulkSmsHelper())->send($donation?->user?->phone_number, "Dear " . $donation->name .", your donation amount " . software()->amountWithCurrency(
                $donation->amount
            ) . ' has been successfully taken. You can download payment slipt by loging in');
        } else if($donation->donor) {
            (new BulkSmsHelper())->send($donation?->donor?->phone_number, "Dear donator, your donation amount " . software()->amountWithCurrency(
                $donation->amount
            ) . ' has been successfully taken. You can download payment slipt by loging in');
        }

        $donation->status = 1;
        $donation->save();

        // message for success 

        return back()->withSuccess(__('Donation approved successfully'));
    }

    public function reject($id)
    {
        goIfUserCan('delete-donation');
        
        $donation = Donation::pending()->findOrFail($id);
        $donation->status = 2;
        $donation->save();

        return back()->withSuccess(__('Donation rejected successfully'));
    }
}
