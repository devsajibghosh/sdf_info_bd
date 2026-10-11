{{-- Mobile (< md) representation of one donation row; tables take over from md up. --}}
<div class="donation-card">
    <div class="d-flex justify-content-between align-items-start gap-2">
        <div>
            <div class="donation-card__amount">{{ System::amountWithCurrency($donation->amount) }}</div>
            <div class="donation-card__category">{{ $donation->category->name ?? '-' }}</div>
        </div>
        <div class="flex-shrink-0">{!! $donation->statusBadge !!}</div>
    </div>
    <div class="d-flex justify-content-between align-items-center gap-2 mt-2">
        <small class="text-muted"><i class="far fa-clock"></i> {{ System::getDateTime($donation->created_at) }}</small>
        @if (! empty($showAction) && $donation->status == 1)
            <a href="{{ route('download.receipt', encrypt($donation->id)) }}" class="btn btn--sm btn-outline--primary flex-shrink-0">
                <i class="fas fa-download"></i> @lang('Receipt')
            </a>
        @endif
    </div>
</div>

@once
    @push('styles')
    <style>
        .donation-card {
            background: #fff;
            border: 1px solid rgba(0, 0, 0, .08);
            border-radius: 10px;
            padding: 12px 14px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, .04);
        }
        .donation-card + .donation-card { margin-top: 10px; }
        .donation-card__amount { font-weight: 600; font-size: 1.05rem; }
        .donation-card__category { color: #6c757d; font-size: .875rem; }
        .donor-table td, .donor-table th { white-space: nowrap; vertical-align: middle; }
        .donor-table td.donor-table__wrap { white-space: normal; min-width: 140px; }
        /* Theme renders page links as fixed 40px circles, which can't hold the
           "Previous/Next" words Laravel shows below sm, so always use the numbered pager. */
        .donor-pagination nav > .d-sm-none { display: none !important; }
        .donor-pagination nav > .d-none {
            display: flex !important;
            flex-direction: column;
            align-items: center;
            gap: 10px;
        }
        @media (min-width: 768px) {
            .donor-pagination nav > .d-none { flex-direction: row; justify-content: space-between; }
        }
        .donor-pagination nav > .d-none > div:first-child p { margin-bottom: 0; }
        .donor-pagination .pagination { flex-wrap: wrap; justify-content: center; row-gap: 6px; margin-bottom: 0; }
    </style>
    @endpush
@endonce
