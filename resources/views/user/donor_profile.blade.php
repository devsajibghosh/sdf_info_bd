@extends('user.layouts.main')

@section('content')
<div class="py-4 py-md-5">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3 mb-md-4">
        <h2 class="mb-0">@lang('Donor Profile')</h2>
        <form action="{{ route('user.donor.logout') }}" method="POST">
            @csrf
            <button type="submit" class="btn btn-danger btn-sm">
                <x-icons.logout />
                @lang('Logout')
            </button>
        </form>
    </div>

    <div class="row gy-4">
        {{-- Profile summary --}}
        <div class="col-12 col-lg-4">
            <div class="card shadow-sm h-100">
                <div class="card-body text-center">
                    <div class="donor-avatar mx-auto mb-3">
                        {{ mb_strtoupper(mb_substr($donor->name ?: $donor->phone_number, 0, 1)) }}
                    </div>
                    <h4 class="mb-1">{{ $donor->name ?: __('Donor') }}</h4>
                    @if ($donor->name_en)
                        <p class="text-muted mb-1">{{ $donor->name_en }}</p>
                    @endif
                    <p class="mb-3"><i class="bi bi-telephone"></i> {{ $donor->phone_number }}</p>

                    <ul class="list-group list-group-flush text-start">
                        <li class="list-group-item donor-detail">
                            <span class="text-muted">@lang('Father Name')</span>
                            <span>{{ $donor->father_name ?: '-' }}</span>
                        </li>
                        <li class="list-group-item donor-detail">
                            <span class="text-muted">@lang('Mother Name')</span>
                            <span>{{ $donor->mother_name ?: '-' }}</span>
                        </li>
                        <li class="list-group-item donor-detail">
                            <span class="text-muted">@lang('Address')</span>
                            <span>{{ $donor->address ?: '-' }}</span>
                        </li>
                        <li class="list-group-item donor-detail">
                            <span class="text-muted">@lang('Donor Since')</span>
                            <span>{{ $donor->created_at ? System::getDateTime($donor->created_at) : '-' }}</span>
                        </li>
                    </ul>
                </div>
            </div>
        </div>

        <div class="col-12 col-lg-8">
            {{-- Donation summary --}}
            <div class="row gy-3 mb-4">
                <div class="col-6 col-md-3">
                    <div class="card shadow-sm h-100 donor-stat"><div class="card-body p-3">
                        <small class="text-muted">@lang('Total Donations')</small>
                        <h5 class="mb-0">{{ $widget['total_donate_count'] }}</h5>
                    </div></div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="card shadow-sm h-100 donor-stat"><div class="card-body p-3">
                        <small class="text-muted">@lang('Total Amount')</small>
                        <h5 class="mb-0">{{ System::amountWithCurrency($widget['total_donate_amount']) }}</h5>
                    </div></div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="card shadow-sm h-100 donor-stat"><div class="card-body p-3">
                        <small class="text-muted">@lang('Last Donation')</small>
                        <h5 class="mb-0">{{ System::amountWithCurrency($widget['last_donation']?->amount ?? 0) }}</h5>
                        <small class="text-muted">{{ $widget['last_donation'] ? System::getDateTime($widget['last_donation']->created_at) : 'N/A' }}</small>
                    </div></div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="card shadow-sm h-100 donor-stat"><div class="card-body p-3">
                        <small class="text-muted">@lang('Pending')</small>
                        <h5 class="mb-0">{{ $widget['pending_count'] }}</h5>
                    </div></div>
                </div>
            </div>

            {{-- Edit profile --}}
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-white"><strong>@lang('Update Profile')</strong></div>
                <div class="card-body">
                    <form action="{{ route('user.donor.profile.submit') }}" method="POST">
                        @csrf
                        <div class="row gy-3">
                            <div class="col-12 col-md-6">
                                <label class="form-label">@lang('Name') <span class="text-danger">*</span></label>
                                <input type="text" class="form-control @error('name') is-invalid @enderror" name="name" maxlength="255" required
                                    value="{{ old('name', $donor->name) }}" />
                                @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-12 col-md-6">
                                <label class="form-label">@lang('Name (English)')</label>
                                <input type="text" class="form-control @error('name_en') is-invalid @enderror" name="name_en" maxlength="255"
                                    value="{{ old('name_en', $donor->name_en) }}" />
                                @error('name_en') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-12 col-md-6">
                                <label class="form-label">@lang('Phone Number')</label>
                                <input type="text" class="form-control" value="{{ $donor->phone_number }}" readonly disabled />
                                <small class="text-muted">@lang('Phone number is used for OTP login and cannot be changed.')</small>
                            </div>
                            <div class="col-12 col-md-6">
                                <label class="form-label">@lang('Father Name')</label>
                                <input type="text" class="form-control @error('father_name') is-invalid @enderror" name="father_name" maxlength="255"
                                    value="{{ old('father_name', $donor->father_name) }}" />
                                @error('father_name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-12 col-md-6">
                                <label class="form-label">@lang('Mother Name')</label>
                                <input type="text" class="form-control @error('mother_name') is-invalid @enderror" name="mother_name" maxlength="255"
                                    value="{{ old('mother_name', $donor->mother_name) }}" />
                                @error('mother_name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-12">
                                <label class="form-label">@lang('Address')</label>
                                <textarea class="form-control @error('address') is-invalid @enderror" name="address" rows="3" maxlength="255">{{ old('address', $donor->address) }}</textarea>
                                @error('address') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                        </div>
                        <div class="d-flex justify-content-end mt-4">
                            <button type="submit" class="btn btn-primary">@lang('Save')</button>
                        </div>
                    </form>
                </div>
            </div>

            {{-- Recent donations --}}
            <div class="card shadow-sm">
                <div class="card-header bg-white d-flex justify-content-between align-items-center">
                    <strong>@lang('Recent Donations')</strong>
                    <a href="{{ route('user.donor.donations') }}" class="btn btn--sm btn-outline--primary">@lang('View All')</a>
                </div>
                {{-- Phones: card list --}}
                <div class="card-body d-md-none">
                    @forelse ($recentDonations as $donation)
                        @include('user.partials.donor_donation_card', ['donation' => $donation])
                    @empty
                        <div class="text-center text-muted py-3">@lang('No donation records found.')</div>
                    @endforelse
                </div>

                {{-- Tablet & desktop: table --}}
                <div class="table-responsive d-none d-md-block">
                    <table class="table table-striped align-middle mb-0 donor-table">
                        <thead class="table-light">
                            <tr>
                                <th>@lang('Amount')</th>
                                <th>@lang('Category')</th>
                                <th>@lang('Time of Donation')</th>
                                <th>@lang('Status')</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($recentDonations as $donation)
                                <tr>
                                    <td class="fw-semibold">{{ System::amountWithCurrency($donation->amount) }}</td>
                                    <td class="donor-table__wrap">{{ $donation->category->name ?? '-' }}</td>
                                    <td>{{ System::getDateTime($donation->created_at) }}</td>
                                    <td>{!! $donation->statusBadge !!}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="text-center text-muted">@lang('No donation records found.')</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    .donor-avatar {
        width: 84px;
        height: 84px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 2rem;
        font-weight: 600;
        color: #fff;
        background: var(--bs-primary, #0d6efd);
    }
    .donor-detail {
        display: flex;
        justify-content: space-between;
        gap: 12px;
        padding-left: 0;
        padding-right: 0;
    }
    .donor-detail > span:last-child { text-align: right; word-break: break-word; }
    .donor-stat h5 { font-size: 1.05rem; word-break: break-word; }
    @media (max-width: 400px) {
        .donor-detail { flex-direction: column; gap: 2px; }
        .donor-detail > span:last-child { text-align: left; }
    }
</style>
@endpush
