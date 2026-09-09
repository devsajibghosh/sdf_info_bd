@extends('admin.layouts.app')

@section('content')

<x-page-header 
    :page_title="__('Payments')" 
    search="true"
/>

<table class="table">
    <thead>
        <th>@lang('User')</th>
        <th>@lang('Payment Method')</th>
        <th>@lang('Transactino No')</th>
        <th>@lang('Amount')</th>
        <th>@lang('Status')</th>
        <th>@lang('Created At')</th>
        <th>@lang('Paid At')</th>
        <th>@lang('bKash Trx')</th>
        <th class="text-end">@lang('Action')</th>
    </thead>

    @forelse ($payments as $payment)
        <tr>
            <td>{{ $payment?->user?->name ?? 'N/A' }}</td>
            <td>{{ $payment->paymentGateway?->name ?? '' }}</td>
            <td>{{ $payment->transaction_no }}</td>
            <td>
                {{ System::amountWithCurrency($payment->amount) }}
            </td>
            <td>@php echo $payment->statusBadge; @endphp</td>
            <td>
                <span>{{ System::getDateTime($payment->created_at) }}</span>
                <br />
                <strong>{{ diffForHumans($payment->created_at) }}</strong>
            </td>
            <td>
                <span>{{ System::getDateTime($payment->paid_at) }}</span>
                <br />
                <strong>{{ diffForHumans($payment->paid_at) }}</strong>
            </td>
            <td>{{ $payment->meta['trx'] ?? 'n/a' }}</td>
            <td class="text-end"> 
                <x-button confirmDelete class="btn-sm btn-danger" href="{{ route('admin.report.payment.delete', $payment->id) }}">
                    <x-icons.delete-v2 />
                    @lang('Delete')
                </x-button>
            </td>
        </tr>
    @empty
        <x-admin-empty-table />
    @endforelse
</table>

<x-admin-paginate :model="$payments" />

@endsection
