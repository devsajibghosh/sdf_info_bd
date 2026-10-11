@extends('user.layouts.main')

@section('content')
<div class="py-4 py-md-5">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3 mb-md-4">
        <div>
            <h2 class="mb-1">@lang('My Donations')</h2>
            <small class="text-muted">
                <i class="fas fa-phone-alt"></i> {{ auth()->guard('donor')->user()->phone_number }}
                &middot; @lang('Total Donations'): {{ $donations->total() }}
            </small>
        </div>
        <a href="{{ route('user.donor.profile') }}" class="btn btn--sm btn-outline--secondary">
            <i class="fas fa-arrow-left"></i> @lang('My Profile')
        </a>
    </div>

    {{-- Phones: card list --}}
    <div class="d-md-none">
        @forelse ($donations as $donation)
            @include('user.partials.donor_donation_card', ['donation' => $donation, 'showAction' => true])
        @empty
            <div class="text-center text-muted py-4">@lang('No donation records found.')</div>
        @endforelse
    </div>

    {{-- Tablet & desktop: table --}}
    <div class="card shadow-sm d-none d-md-block">
        <div class="table-responsive">
            <table class="table table-striped align-middle mb-0 donor-table">
                <thead class="table-light">
                    <tr>
                        <th>@lang('Amount')</th>
                        <th>@lang('Category')</th>
                        <th>@lang('Time of Donation')</th>
                        <th>@lang('Status')</th>
                        <th class="text-end">@lang('Action')</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($donations as $donation)
                        <tr>
                            <td class="fw-semibold">{{ System::amountWithCurrency($donation->amount) }}</td>
                            <td class="donor-table__wrap">{{ $donation->category->name ?? '-' }}</td>
                            <td>{{ System::getDateTime($donation->created_at) }}</td>
                            <td>{!! $donation->statusBadge !!}</td>
                            <td class="text-end">
                                @if ($donation->status == 1)
                                    <a href="{{ route('download.receipt', encrypt($donation->id)) }}" class="btn btn--sm btn-outline--primary">
                                        <i class="fas fa-download"></i> @lang('Receipt')
                                    </a>
                                @else
                                    -
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center text-muted">@lang('No donation records found.')</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    @if ($donations->hasPages())
        <div class="mt-3 donor-pagination">
            {{ $donations->onEachSide(1)->links() }}
        </div>
    @endif
</div>
@endsection
