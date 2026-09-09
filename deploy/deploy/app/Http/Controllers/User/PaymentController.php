<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\AdminNotification;
use App\Models\Donation;
use App\Models\DonationCategory;
use App\Models\Payment;
use App\Models\PaymentGateway;
use App\Services\GatewayFactory;
use App\Services\Gateways\ManualPaymentGateway;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;

class PaymentController extends Controller
{
    public function downloadPdf($donationId)
    {
        $donation = Donation::findOrFail($donationId);

        $pdf = Pdf::loadView('user.payment.receipt', compact('donation'));

        return $pdf->download('donation-receipt-' . $donation->id . '.pdf');
    }

    public function paymentHistory()
    {
        $title = 'Donations History';

        $donations = auth()->user()->donations()->latest()->paginate();

        return view('user.payment.history', compact('title', 'donations'));
    }

    public function newPayment(Request $request)
    {
        $title = __('New Donation');

        $paymentGateways = PaymentGateway::active()->where('key', '!=', 'cash')->get();

        $donationCategories = DonationCategory::active()->get();

        return view('user.payment.new', compact('title', 'paymentGateways', 'donationCategories'));
    }

    public function notify(Request $request, $key)
    {
        try {
            $class = \App\Helpers\GatewayHelper::paymentGateways($key)['class'];

            return (new $class())->verify($request);
        } catch (\Exception $e) {
            return (new ManualPaymentGateway($key))->verify($request);
        }
    }

    public function paymentInsert(Request $request)
    {
        $request->validate([
            'amount'               => 'required|numeric|gt:0',
            'donation_category_id' => 'required|exists:donation_categories,id',
            'payment_gateway_id'   => 'required|exists:payment_gateways,id'
        ]);

        $paymentGateway = PaymentGateway::where('status', 1)->findOrFail($request->payment_gateway_id);

        $gatewayCode = $paymentGateway->key;

        $user = auth()->user();

        // payment log
        $payment                     = new Payment();
        $payment->user_id            = $user->id;
        $payment->status             = 'pending';
        $payment->amount             = $request->amount;
        $payment->currency           = generalSetting('currency');
        $payment->transaction_no     = generateTransactionId();
        $payment->payment_gateway_id = $request->payment_gateway_id;
        $payment->method             = $gatewayCode;
        $payment->save();

        // create the donaiton
        $donation                       = new Donation();
        $donation->amount               = $request->amount;
        $donation->user_id              = $user->id;
        $donation->payment_id           = $payment->id;
        $donation->email                = $user->email;
        $donation->phone_number         = $user->phone_number;
        $donation->donation_category_id = $request->donation_category_id;
        $donation->status = $payment?->paymentGateway?->manual ? 0 : 1;
        $donation->save();

        $adminNotification          = new AdminNotification();
        $adminNotification->user_id = $user->id;
        $adminNotification->link    = route('admin.donation.list') . '?search='.$payment->transaction_no;
        $adminNotification->details = __('New donation received');
        $adminNotification->save();

        $gateway = GatewayFactory::make($gatewayCode);
        return $gateway->create($payment);
    }

    public function paymentSuccess(Request $request)
    {
        $payment = Payment::findOrFail($request->pid);

        if ($payment->status === 'success') {
            return redirect()->route('user.dashboard')->with('info', 'Already processed');
        }

        $gateway = GatewayFactory::make($payment->method);

        if ($gateway->verify($payment)) {
            $payment->status = 'success';
            $payment->save();

            $user = auth()->user();
            $user->balance += $payment->amount;
            $user->save();

            return redirect()->route('user.dashboard')->with('success', 'Payment successful!');
        }

        return redirect()->route('user.dashboard')->with('error', 'Payment failed or incomplete.');
    }
}
