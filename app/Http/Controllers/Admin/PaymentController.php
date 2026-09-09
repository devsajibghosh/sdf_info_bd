<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Payment;

class PaymentController extends Controller
{
    public function list()
    {
        goIfUserCan('payment-report');
        $title = 'Payments';

        $payments = Payment::with(['user:id,name', 'paymentGateway:id,name'])
            ->latest()
            ->searching(['transaction_no'])
            ->paginate(100);

        return view('admin.payment.list', compact('title', 'payments'));
    }

    public function deletePayment($id)
    {
        goIfUserCan('payment-report');
        $payment = Payment::findOrFail($id);
        $payment->delete();
        return back()->withSuccess(__('Payment deleted sucessfully'));
    }
}
