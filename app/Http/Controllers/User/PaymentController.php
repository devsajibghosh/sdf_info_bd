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
use App\Services\ReceiptPdfService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class PaymentController extends Controller
{
    public function downloadPdf($donationId, ReceiptPdfService $receiptPdf)
    {
        $donation = Donation::where('user_id', auth()->id())->findOrFail($donationId);

        try {
            return $receiptPdf->download(
                'user.payment.receipt',
                compact('donation'),
                'donation-receipt-' . $donation->id . '.pdf'
            );
        } catch (\Throwable $e) {
            Log::error('Donation receipt PDF generation failed.', [
                'donation_id' => $donation->id,
                'error' => $e->getMessage(),
            ]);

            return back()->with('error', __('Unable to generate the donation receipt. Please try again later.'));
        }
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

        $paymentGateways = PaymentGateway::active()->hidden()->get();

        $donationCategories = DonationCategory::active()->get();

        return view('user.payment.new', compact('title', 'paymentGateways', 'donationCategories'));
    }

    public function notify(Request $request, $key)
    {
        // Only fall back to ManualPaymentGateway when $key doesn't resolve to a
        // real gateway class at all. Previously this wrapped the resolved
        // gateway's verify() call too, so any exception thrown *during*
        // verification (e.g. an automatic gateway rejecting a malformed/tampered
        // callback) was silently swallowed and misrouted into
        // ManualPaymentGateway::verify(), which then crashed on a missing
        // "payment_id" field instead of surfacing the real error.
        $gateway = \App\Helpers\GatewayHelper::paymentGateways($key);

        if (is_array($gateway) && !empty($gateway['class']) && class_exists($gateway['class'])) {
            return (new $gateway['class']())->verify($request);
        }

        return (new ManualPaymentGateway($key))->verify($request);
    }

    public function paymentInsert(Request $request)
    {
        $request->validate([
            'amount'               => 'required|numeric|gte:20',
            'donation_category_id' => 'required|exists:donation_categories,id',
            'payment_gateway_id'   => 'required|exists:payment_gateways,id',
            'field_collection' => 'required|in:0,1'
        ]);

        // Automatic gateways only: manual gateway ids can never be submitted here,
        // even via a crafted direct POST — they must go through the admin's
        // manual donation entry flow instead.
        $paymentGateway = PaymentGateway::where('status', 1)->where('manual', 0)->findOrFail($request->payment_gateway_id);

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
        $donation->field_collection     = $request->field_collection;
        // Stays pending until the gateway actually confirms the payment.
        $donation->status = 0;
        $donation->save();

        $adminNotification          = new AdminNotification();
        $adminNotification->user_id = $user->id;
        $adminNotification->link    = route('admin.donation.list') . '?search='.$payment->transaction_no;
        $adminNotification->details = __('New donation received');
        $adminNotification->save();

        try {
            $gateway = GatewayFactory::make($gatewayCode);
            return $gateway->create($payment);
        } catch (\Throwable $e) {
            Log::error('Payment gateway initiation failed.', [
                'payment_id' => $payment->id,
                'gateway' => $gatewayCode,
                'error' => $e->getMessage(),
            ]);

            $payment->status = 'failed';
            $payment->save();

            $donation->status = 2;
            $donation->save();

            return back()->withError(__('Unable to start the payment. Please try again in a moment.'));
        }
    }

    public function paymentSuccess(Request $request)
    {
        $payment = Payment::findOrFail($request->pid);

        abort_unless($payment->user_id === auth()->id(), 403);

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
