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
                    {{-- Section 1: manual gateways (approved donations only, SSLCommerz excluded) --}}
                    <tr class="section-row"><td colspan="2">@lang('Section 1: Manual Donations')</td></tr>
                    @foreach([
                        ['1', __('bKash Donation'), $bkashDonation],
                        ['2', __('Nagad Donation'), $nagadDonation],
                        ['3', __('Rocket Donation'), $rocketDonation],
                        ['4', __('Bank Donation'), $bankDonation],
                        ['5', __('Cash Donation'), $cashDonation],
                        ['6', __('Goods Donation'), $goodsDonation],
                    ] as [$no, $label, $amount])
                        <tr class="gateway-row">
                            <td><span class="dot"></span>{{ $no }}. {{ $label }}</td>
                            <td class="fw-bold text-end">{{ number_format($amount, 2) }}</td>
                        </tr>
                    @endforeach
                    <tr class="highlight-row">
                        <td class="fw-bold text-primary">7. @lang('Total Donation') <small class="text-muted">(1-6)</small></td>
                        <td class="fw-bold text-primary text-end">{{ number_format($totalDonation, 2) }}</td>
                    </tr>
                    <tr class="expense-text">
                        <td>8. @lang('CO Charge') <small class="text-muted">(@lang('1.5% of') 1-3)</small></td>
                        <td class="text-danger text-end">- {{ number_format($coCharge, 2) }}</td>
                    </tr>
                    <tr class="net-donation-row">
                        <td class="fw-bold">9. @lang('Net Donation') <small class="text-muted">(7 - 8)</small></td>
                        <td class="fw-bold text-end">{{ number_format($netDonation, 2) }}</td>
                    </tr>

                    {{-- Section 2: SSLCommerz (confirmed + approved only) --}}
                    <tr class="section-row"><td colspan="2">@lang('Section 2: SSLCommerz')</td></tr>
                    <tr class="gateway-row">
                        <td><span class="dot"></span>10. @lang('SSLCommerz Gross Donation')</td>
                        <td class="fw-bold text-end">{{ number_format($sslCommerzGross, 2) }}</td>
                    </tr>
                    <tr class="expense-text">
                        <td>11. @lang('SSLCommerz Fee')<br><small class="text-muted">@lang('MFS 2.5%'): {{ number_format($sslMfsFee, 2) }} + @lang('Card 3.5%'): {{ number_format($sslCardFee, 2) }}</small></td>
                        <td class="text-danger text-end">- {{ number_format($sslCommerzFee, 2) }}</td>
                    </tr>
                    <tr class="gateway-row">
                        <td><span class="dot"></span>12. @lang('SSLCommerz Unclassified Channel (fee not calculated)')</td>
                        <td class="text-end">{{ number_format($sslUnknownGross, 2) }}</td>
                    </tr>
                    <tr class="net-donation-row">
                        <td class="fw-bold">13. @lang('SSLCommerz Net Donation') <small class="text-muted">(10 - 11)</small></td>
                        <td class="fw-bold text-end">{{ number_format($sslCommerzNet, 2) }}</td>
                    </tr>

                    {{-- Section 3: balances --}}
                    <tr class="section-row"><td colspan="2">@lang('Section 3: Balance')</td></tr>
                    <tr class="highlight-row">
                        <td class="fw-bold text-primary">14. @lang('Total Net Donation') <small class="text-muted">(9 + 13)</small></td>
                        <td class="fw-bold text-primary text-end">{{ number_format($totalNetDonation, 2) }}</td>
                    </tr>
                    <tr>
                        <td>15. @lang('SDF Taken Loan')</td>
                        <td class="text-end">+ {{ number_format($sdfTakenLoan, 2) }}</td>
                    </tr>
                    <tr class="expense-text">
                        <td>16. @lang('Total Expense')</td>
                        <td class="text-danger text-end">- {{ number_format($totalExpense, 2) }}</td>
                    </tr>
                    <tr class="grand-total-row">
                        <td>17. @lang('Net Balance') <small>(14 + 15 - 16)</small></td>
                        <td class="text-end">{{ number_format($netBalance, 2) }}</td>
                    </tr>
                    <tr class="bank-row">
                        <td class="text-muted">18. @lang('Bank Balance')</td>
                        <td class="text-dark text-end">{{ number_format($bankBalance, 2) }}</td>
                    </tr>
                    <tr class="bank-row">
                        <td class="text-muted">19. @lang('SSLCommerz Balance')</td>
                        <td class="text-dark text-end">{{ number_format($sslCommerzBalance, 2) }}</td>
                    </tr>
                    <tr class="bank-row border-0">
                        <td class="fw-bold">20. @lang('MFS Balance') <small class="text-muted">(17 - 18 - 19)</small></td>
                        <td class="fw-bold text-dark text-end">{{ number_format($mfsBalance, 2) }}</td>
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

    .section-row td {
        background-color: #f1f4f9;
        color: #4e5e7a;
        font-weight: 700;
        font-size: 0.8rem;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        padding: 0.6rem 1.5rem;
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