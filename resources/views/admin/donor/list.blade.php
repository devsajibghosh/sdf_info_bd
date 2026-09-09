@extends('admin.layouts.app')

@section('content')
    <x-page-header :page_title="__('Donors')" search="true" />
    
<div class="mb-3 text-end">
<x-button 
    href="{{ route('admin.donor.download.max.donor') }}" 
    class="btn-primary btn-sm"
>
<span>{{ __('Download Max(20D)') }}</span>
</x-button>
</div>

        <table class="table">
            <thead>
                <th>@lang('Name')</th>
                <th>@lang('Phone Number')</th>
                <th>@lang('Total Donation Amount')</th>
                <th>@lang('Number of Donations')</th>
                <th>@lang('Last Donate')</th>
                <th class="text-end">@lang('Action')</th>
            </thead>

            @forelse ($donors as $donor)
                <tr>
                    
  <td>
            @php
                if ($donor->name) {
                    $displayName = $donor->name;
                } else {
                    $user = \App\Models\User::where('phone_number', $donor->phone_number)->first();
                    $displayName = $user ? $user->name : '-';
                }
            @endphp
            {{ $displayName }}

        </td>
                    
                    
                    <td>{{ $donor->phone_number ?? '-' }}</td>
                    <td>
                        {{ System::amountWithCurrency(
                            $donor->total_donations_amount
                        ) }}
                    </td>
                    <td>{{ $donor->total_donations_count ?? '-' }}</td>
                    <td>
                        {{ $donor->lastDonation 
                            ? System::amountWithCurrency($donor->lastDonation->amount) 
                            : '-' }}
                            <br>
                        @lang('at') {{ software()->getDateTime($donor?->lastDonation?->created_at) }}
                    </td>
                    <td class="text-end">
                        <x-button href="{{ route('admin.donor.edit', ['id' => $donor->id, 'page' => request()->get('page', 1)]) }}">
                            <x-icons.eye />
                            <span class="ms-1">@lang('View')</span>
                        </x-button>
                    </td>
                </tr>
            @empty
                <x-admin-empty-table />
            @endforelse
    </table>

    <x-admin-paginate :model="$donors" />
@endsection