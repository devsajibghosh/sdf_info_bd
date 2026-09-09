@extends('user.layouts.main')

@section('content')
<div class="history-container">
    {{-- Page Header --}}
    <div class="page-header">
        <div class="header-content">
            <h1>@lang('Donation History')</h1>
            <p>@lang('A complete record of your contributions. Thank you for your continued support.')</p>
        </div>
    </div>

    <div class="card-table-wrapper">
        <div class="table-responsive">
            <table class="table modern-table">
                <thead>
                    <tr>
                        <th>@lang('Donor Details')</th>
                        <th>@lang('Transaction ID')</th>
                        <th>@lang('Amount')</th>
                        <th>@lang('Category')</th>
                        <th>@lang('Date')</th>
                        <th>@lang('Status')</th>
                        <th class="text-end">@lang('Action')</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($donations as $donation)
                        <tr>
                            <td data-label="Donor Details">
                                <div class="user-info">
                                    <span class="user-email">{{ $donation->email ?? 'N/A' }}</span>
                                    <span class="user-phone">{{ $donation->phone_number ?? 'N/A' }}</span>
                                </div>
                            </td>
                            <td data-label="Transaction ID">
                                <span class="trx-id">{{ $donation?->payment?->transaction_no ?? '-' }}</span>
                            </td>
                            <td data-label="Amount">
                                <span class="amount">{{ System::amountWithCurrency($donation->amount) }}</span>
                            </td>
                            <td data-label="Category">{{ $donation->category->name ?? '-' }}</td>
                            <td data-label="Date">{{ System::getDateTime($donation->created_at) }}</td>
                            <td data-label="Status">
                                {{-- This will render the badge from your controller/model --}}
                                @php echo $donation->statusBadge @endphp
                            </td>
                            <td data-label="Action" class="text-end">
                                @if ($donation->status == 1)
                                    <a href="{{ route('download.receipt', encrypt($donation->id)) }}" class="btn-action" title="@lang('Download Receipt')">
                                        <i class="fas fa-download"></i>
                                        <span>@lang('Download')</span>
                                    </a>
                                @else
                                    <span class="action-placeholder">-</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7">
                                <div class="empty-state">
                                    <i class="fas fa-file-invoice-dollar"></i>
                                    <h2>@lang('No Donation Records Found')</h2>
                                    <p>@lang('When you make a donation, it will appear here.')</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Pagination --}}
    @if ($donations->hasPages())
        <div class="pagination-wrapper">
            {!! $donations->links() !!}
        </div>
    @endif
</div>
@endsection

@push('styles')
{{-- Make sure you have Font Awesome loaded in your main layout --}}
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
<style>
    :root {
        --primary-color: #007bff;
        --success-color: #28a745;
        --warning-color: #ffc107;
        --danger-color: #dc3545;
        --background-color: #f7f8fc;
        --card-bg: #ffffff;
        --text-color: #6c757d;
        --heading-color: #343a40;
        --border-color: #e9ecef;
        --border-radius: 10px;
        --box-shadow: 0 4px 12px rgba(0, 0, 0, 0.06);
    }

    .history-container {
        max-width: 1200px;
        margin: 0 auto;
        padding: 2.5rem 1.5rem;
        font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
    }

    /* Page Header */
    .page-header {
        margin-bottom: 2rem;
    }

    .page-header h1 {
        font-size: 2rem;
        font-weight: 700;
        color: var(--heading-color);
        margin-bottom: 0.25rem;
    }

    .page-header p {
        font-size: 1.1rem;
        color: var(--text-color);
    }

    .card-table-wrapper {
        background-color: var(--card-bg);
        border-radius: var(--border-radius);
        box-shadow: var(--box-shadow);
        padding: 1rem;
        border: 1px solid var(--border-color);
    }

    /* Modern Table Styles */
    .modern-table {
        width: 100%;
        border-collapse: collapse;
    }

    .modern-table thead {
        border-bottom: 2px solid var(--border-color);
    }

    .modern-table th {
        padding: 1rem 1.25rem;
        text-align: left;
        font-weight: 600;
        font-size: 0.875rem;
        color: var(--text-color);
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }
    
    .modern-table tbody tr {
        border-bottom: 1px solid var(--border-color);
        transition: background-color 0.2s ease;
    }

    .modern-table tbody tr:last-child {
        border-bottom: none;
    }

    .modern-table tbody tr:hover {
        background-color: #f8f9fa;
    }

    .modern-table td {
        padding: 1.25rem;
        vertical-align: middle;
        font-size: 0.95rem;
        color: var(--text-color);
    }
    
    .text-end { text-align: right !important; }

    /* Custom cell styles */
    .user-info {
        display: flex;
        flex-direction: column;
    }
    .user-info .user-email {
        font-weight: 600;
        color: var(--heading-color);
    }
    .user-info .user-phone {
        font-size: 0.9em;
    }
    
    .trx-id {
        font-family: 'Courier New', Courier, monospace;
        font-weight: 600;
        color: var(--primary-color);
    }
    
    .amount {
        font-weight: 700;
        font-size: 1.05rem;
        color: var(--heading-color);
    }

    /* Status Badges - Assuming your PHP generates .badge class */
    .badge {
        padding: 0.4em 0.8em;
        border-radius: 50px;
        font-weight: 600;
        font-size: 0.8rem;
    }
    .badge-success, .bg-success {
        background-color: #e9f7ec !important;
        color: #28a745 !important;
    }
    .badge-warning, .bg-warning {
        background-color: #fff8e6 !important;
        color: #ffc107 !important;
    }
    .badge-danger, .bg-danger {
        background-color: #fbebed !important;
        color: #dc3545 !important;
    }
    
    /* Action Button */
    .btn-action {
        background-color: #f1f1f1;
        color: var(--heading-color);
        border: 1px solid #ddd;
        border-radius: 6px;
        padding: 0.5rem 1rem;
        text-decoration: none;
        font-weight: 600;
        font-size: 0.9rem;
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        transition: background-color 0.2s, color 0.2s, border-color 0.2s;
    }
    .btn-action:hover {
        background-color: var(--primary-color);
        color: #fff;
        border-color: var(--primary-color);
    }
    
    /* Empty State */
    .empty-state {
        padding: 4rem;
        text-align: center;
    }
    .empty-state i {
        font-size: 3rem;
        color: #ced4da;
        margin-bottom: 1rem;
    }
    .empty-state h2 {
        color: var(--heading-color);
        font-size: 1.5rem;
    }
    .empty-state p {
        color: var(--text-color);
    }
    
    /* Pagination */
    .pagination-wrapper {
        margin-top: 2rem;
        display: flex;
        justify-content: center;
    }
    /* Add styles for your specific pagination component if needed */

    /* Responsive Transformation to Cards */
    @media (max-width: 992px) {
        .modern-table thead {
            display: none; /* Hide table header on mobile */
        }
        .modern-table tr {
            display: block;
            margin-bottom: 1.5rem;
            border-radius: var(--border-radius);
            box-shadow: 0 2px 6px rgba(0,0,0,0.08);
            border: 1px solid var(--border-color);
            background: var(--card-bg);
        }
        .modern-table td {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0.75rem 1.25rem;
            border-bottom: 1px solid var(--border-color);
            text-align: right;
        }
        .modern-table tr td:last-child {
            border-bottom: none;
        }
        .modern-table td::before {
            content: attr(data-label);
            font-weight: 600;
            color: var(--heading-color);
            text-align: left;
            margin-right: 1rem;
        }
        
        .modern-table .text-end { text-align: right !important; }
        .modern-table td.action-cell, .modern-table .user-info {
            justify-content: flex-end; /* Align actions to the right */
        }
    }
</style>
@endpush