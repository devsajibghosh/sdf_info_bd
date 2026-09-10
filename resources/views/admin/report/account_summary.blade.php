@extends('admin.layouts.app')

@section('content')
    <x-page-header :page_title="__('Financial Account Summary')"></x-page-header>

    <div class="summary-container">
        <div class="summary-card">
            <div class="card-header-custom">
                <h4 class="m-0"><i class="fas fa-wallet me-2"></i>@lang('Balance Overview')</h4>
                <small class="text-muted">@lang('Real-time data synchronization')</small>
            </div>
            
            <table class="summary-table">
                <thead>
                    <tr>
                        <th>@lang('Description')</th>
                        <th class="text-end">@lang('Amount (BDT)')</th>
                    </tr>
                </thead>
                <tbody>
                    {{-- Individual Gateway Breakdown --}}
                    @foreach($gatewaySums as $gateway)
                        <tr class="gateway-row">
                            <td>
                                <span class="dot"></span>
                                {{ $gateway->display_name ?? __('Unknown') }} @lang('Donation')
                            </td>
                            <td class="fw-bold text-end">{{ number_format($gateway->total, 2) }}</td>
                        </tr>
                    @endforeach
                    
                    <!-- cash donation amount-->
                    
                    <tr>
                        <td>
                            <span class="dot"></span>
                            @lang('Cash Donation')
                        </td>
                        <td class="text-success text-end">{{ number_format($cashDonation, 2) }}</td>
                    </tr>
                    
                    <tr class="highlight-row bg-dark">
                        <td class="fw-bold text-primary">@lang('Total Donation')</td>
                        <td class="fw-bold text-primary text-end">{{ number_format($totalDonation, 2) }}</td>
                    </tr>

                    <tr class="divider-row"><td colspan="2"></td></tr>

                    {{-- SSLCommerz Gross / Fee / Net breakdown, derived from the actual
                         card_brand SSLCommerz returned per transaction: MFS (bKash/Nagad/
                         Rocket/etc.) @ 2.5%, Card (VISA/MASTER/AMEX) @ 3.5%. --}}
                    <tr class="gateway-row">
                        <td><span class="dot"></span>@lang('SSLCommerz Gross Donation')</td>
                        <td class="fw-bold text-end">{{ number_format($sslCommerzGross, 2) }}</td>
                    </tr>
                    <tr class="expense-text">
                        <td>@lang('SSLCommerz Fee')<br><small class="text-muted">@lang('MFS 2.5%'): {{ number_format($sslMfsFee, 2) }} + @lang('Card 3.5%'): {{ number_format($sslCardFee, 2) }}</small></td>
                        <td class="text-danger text-end">- {{ number_format($sslCommerzFee, 2) }}</td>
                    </tr>
                    <tr class="gateway-row">
                        <td><span class="dot"></span>@lang('SSLCommerz Net Donation')</td>
                        <td class="fw-bold text-end">{{ number_format($sslCommerzNet, 2) }}</td>
                    </tr>
                    @if($sslUnknownGross > 0)
                        <tr class="gateway-row">
                            <td><span class="dot"></span>@lang('SSLCommerz Unclassified Channel (fee not calculated)')</td>
                            <td class="text-end">{{ number_format($sslUnknownGross, 2) }}</td>
                        </tr>
                    @endif

                    <tr class="divider-row"><td colspan="2"></td></tr>

                    {{-- Deductions Section --}}
                    <tr class="expense-text bg-warning">
                        <td>@lang('Total CO Charge')<br><small class="text-muted">(@lang('includes SSLCommerz fee'))</small></td>
                        <td class="text-danger text-end">- {{ number_format($totalCoCharge, 2) }}</td>
                    </tr>
                    
                    <tr class="net-donation-row bg-dark">
                        <td class="fw-bold text-white">@lang('Net Donation')</td>
                        <td class="fw-bold text-end text-white">{{ number_format($netDonation, 2) }}</td>
                    </tr>
                    
                    <!--<tr class="divider-row"><td colspan="2"></td></tr>-->

                    {{-- External Factors --}}
                    <tr class="bg-warning">
                        <td class="text-white">@lang('SDF Taken Loan')</td>
                        <td class="text-white text-end">+ {{ number_format($sdfTakenLoan, 2) }}</td>
                    </tr>
                    <tr class="expense-text">
                        <td>@lang('Total Expense')</td>
                        <td class="text-danger text-end">- {{ number_format($totalExpense, 2) }}</td>
                    </tr>

                    {{-- The Final Balance --}}
                    <tr class="grand-total-row">
                        <td>@lang('Net Balance')</td>
                        <td class="text-end">{{ number_format($netBalance, 2) }}</td>
                    </tr>

                    <tr class="divider-row"><td colspan="2"></td></tr>

                    {{-- Physical/Bank Locations --}}
                    <tr class="bank-row">
                        <td class="text-muted">@lang('Bank Balance')</td>
                        <td class="text-dark text-end">{{ number_format($bankBalance, 2) }}</td>
                    </tr>
                    <tr class="bank-row border-0">
                        <td class="text-muted">@lang('Mobile Banking Balance')</td>
                        <td class="text-dark text-end">{{ number_format($finalB, 2) }}</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
@endsection



@push('styles')



<style>
    :root {
        --primary-color: #4e73df;
        --success-color: #1cc88a;
        --danger-color: #e74a3b;
        --bg-light: #f8f9fc;
        --border-color: #eaecf4;
    }

    .summary-container {
        padding: 1.5rem;
        display: flex;
        justify-content: center;
    }

    .summary-card {
        background: #ffffff;
        border-radius: 12px;
        box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.1);
        width: 100%;
        max-width: 700px;
        overflow: hidden;
        border: 1px solid var(--border-color);
    }

    .card-header-custom {
        padding: 1.25rem 1.5rem;
        background-color: var(--bg-light);
        border-bottom: 1px solid var(--border-color);
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .summary-table {
        width: 100%;
        border-collapse: collapse;
    }

    .summary-table th {
        background-color: #f1f4f9;
        color: #4e5e7a;
        text-transform: uppercase;
        font-size: 0.75rem;
        letter-spacing: 0.05em;
        padding: 1rem 1.5rem;
        border-bottom: 2px solid var(--border-color);
    }

    .summary-table td {
        padding: 1rem 1.5rem;
        font-size: 0.95rem;
        color: #5a5c69;
        border-bottom: 1px solid var(--border-color);
    }

    /* Row Styles */
    .gateway-row td { color: #858796; }
    .dot {
        height: 8px;
        width: 8px;
        background-color: #FF350D;
        border-radius: 50%;
        display: inline-block;
        margin-right: 8px;
    }

    .highlight-row { background-color: #f0f3ff; }
    .net-donation-row { background-color: #f8f9fa; }
    
    .grand-total-row td {
        background-color: #343a40;
        color: #ffffff !important;
        font-weight: 700;
        font-size: 1.2rem;
    }

    .divider-row td {
        padding: 0.5rem;
        background-color: var(--bg-light);
        border: none;
    }

    .bank-row td {
        font-size: 0.85rem;
        background: #fff;
    }

    /* Utility Classes */
    .text-danger { color: var(--danger-color) !important; }
    .text-success { color: var(--success-color) !important; }
    .text-primary { color: var(--primary-color) !important; }
    
    
    .summary-table td:last-child, 
    .summary-table th:last-child {
        text-align: right !important; /* Forces right alignment */
        padding-right: 1.5rem;
        /* Use a font that keeps all numbers the same width */
        font-family: 'SFMono-Regular', Consolas, 'Liberation Mono', Menlo, monospace;
        letter-spacing: -0.02em;
    }

    /* Specific fix for the Grand Total row to maintain alignment */
    .grand-total-row td:last-child {
        text-align: right !important;
    }

    /* Ensure Bootstrap utility works if present */
    .text-end {
        text-align: right !important;
    }
    
    
</style>


@endpush