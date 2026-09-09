<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PaymentGateway;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rules\Unique;

class PaymentGatewayController extends Controller
{
    public function list()
    {
        goIfUserCan('view-payment-gateways');
        $title = 'Payment Gateways';

        $paymentGateways = PaymentGateway::active()->automatic()->paginate();

        return view('admin.payment_gateway.list', compact('title', 'paymentGateways'));
    }

    public function manualList()
    {
        goIfUserCan('view-payment-gateways');
        $title = 'Manual Payment Gateways';

        $paymentGateways = PaymentGateway::active()->manual()->paginate();

        return view('admin.payment_gateway.list', compact('title', 'paymentGateways'));
    }

    public function new()
    {
        goIfUserCan('view-payment-gateways');
        $title = 'Add Payment Gateway';

        return view('admin.payment_gateway.form', compact('title'));
    }

    public function edit($key)
    {
        goIfUserCan('view-payment-gateways');
        $title = 'Edit Payment Gateway';

        $paymentGateway = PaymentGateway::active()->where('key', $key)->firstOrFail();

        if ($paymentGateway->manual) {
            return view('admin.payment_gateway.form', compact('title', 'paymentGateway'));
        } else {
            return view('admin.payment_gateway.edit', compact('title', 'paymentGateway'));
        }
    }

    public function save(Request $request, $key = null)
    {
        goIfUserCan('view-payment-gateways');
        $request->validate([
            'name'       => 'required|max:255|unique:payment_gateways,name,' . $key . ',key',
            'short_desc' => 'nullable|max:255',
            'image'      => 'nullable|image|mimes:jpeg,png,jpg,svg|max:2048',
            'config'     => 'nullable',
        ]);

        if ($key) {
            $paymentGateway = PaymentGateway::where('key', $key)->firstOrFail();
        } else {
            $paymentGateway = new PaymentGateway();
        }

        $paymentGateway->name = $request->name;
        $paymentGateway->key  = str($request->name)->slug();

        if ($request->hasFile('image')) {
            if ($key && $paymentGateway->image && Storage::disk('public')->exists($paymentGateway->image)) {
                Storage::disk('public')->delete($paymentGateway->image);
            }

            $imagePath = $request->file('image')->store('public/assets/images/' . \Illuminate\Support\Str::slug($key) . '.png');
            $paymentGateway->image = $imagePath;
        }

        $config                     = $request->config;
        $paymentGateway->config     = $config;
        $paymentGateway->short_desc = $request->short_desc;

        if (!$key or $paymentGateway?->manual) {
            $paymentGateway->instruction = $request->instruction;
            $paymentGateway->manual      = 1;
        }

        $paymentGateway->save();

        return back()->withSuccess(__('Payment gateway saved successfully.'));
    }
}
