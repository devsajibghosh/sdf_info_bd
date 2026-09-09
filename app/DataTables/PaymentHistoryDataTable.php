<?php

namespace App\DataTables;

use App\Facades\System;
use App\Helpers\SystemHelper;
use App\Models\Payment;

class PaymentHistoryDataTable extends DataTableBase
{
    protected $tableId = 'payment-history-table';
    
    public function dataTable($query)
    {
        return datatables()
            ->eloquent($query)
            ->addColumn('user', fn($payment) => view('admin.payment.user-cell', compact('payment'))->render())
            ->addColumn('payment_gateway', fn($payment) => view('admin.payment.image-cell', compact('payment'))->render())
            ->addColumn('created_at', fn($payment) => $payment->created_at->format('d/m/Y'))
            ->addColumn('amount', fn($payment) => System::amountWithCurrency($payment->amount))
            ->addColumn('paid_at', fn($payment) => $payment->paid_at ?? 'N/A')
            ->editColumn('status', fn($payment) => $payment->statusBadge)
            ->addColumn('action', function ($payment) {
                return view('admin.payment.action', compact('payment'))->render();
            })
            ->rawColumns(['status', 'payment_gateway', 'user', 'action']);
    }

    public function query(Payment $model)
    {
        return $model->newQuery()
            ->with(['user', 'paymentGateway']) 
            ->select(['id', 'amount', 'user_id', 'payment_gateway_id', 'paid_at', 'transaction_no', 'status', 'created_at', 'paid_at']);
    }

    protected function getColumns()
    {
        return [
            ['data' => 'user', 'name' => 'user_id', 'title' => 'User'],
            ['data' => 'payment_gateway', 'name' => 'payment_gateway', 'title' => 'Payment Gateway'],
            ['data' => 'transaction_no', 'name' => 'transaction_no', 'title' => 'Transaction No'],
            ['data' => 'amount', 'name' => 'amount', 'title' => 'Amount'],
            ['data' => 'status', 'name' => 'status', 'title' => 'Status'],
            ['data' => 'created_at', 'name' => 'created_at', 'title' => 'Created At'],
            ['data' => 'paid_at', 'name' => 'paid_at', 'title' => 'Paid At'],
            ['data' => 'action', 'name' => 'action', 'title' => 'Actions', 'orderable' => false, 'searchable' => false],
        ];
    }
}
