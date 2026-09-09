
@extends('admin.layouts.app')

@section('content')
    <x-page-header 
        :page_title="__('Donation Categories')" 
        :add_route="route('admin.donation_category.create')"
        search="true"
    />

    <table class="table">
        <thead>
            <th>@lang('Name')</th>
            <th>@lang('Status')</th>
            <th>@lang('Total Donation')</th>
            <th>@lang('Image')</th>
            <th>@lang('Created At')</th>
            <th>@lang('Last Modifed At')</th>
            <th class="text-end">@lang('Action')</th>
        </thead>

        <tbody>
            @forelse ($donationCategories as $donationCategory)
                <tr>
                    <td>
                        <a href="{{ route('admin.donation_category.list') }}?search={{ $donationCategory->name }}">{{ $donationCategory->name }}</a>
                    </td>
                    <td>@php echo $donationCategory->statusBadge; @endphp</td>
                    <td>{{ System::amountWithCurrency($donationCategory->donations_sum_amount) }}</td>
                    <td>
                        @if ($donationCategory->image)
                            <img src="{{ asset('storage/' . $donationCategory->image) }}" alt="{{ $donationCategory->name }}" height="40">
                        @else
                            {{ __('N/A') }}
                        @endif
                    </td>
                    <td>{{ System::getDateTime($donationCategory->created_at) }}</td>
                    <td>{{ System::getDateTime($donationCategory->updated_at) }}</td>
                    <td class="text-end">
                        <x-button class="btn-sm btn-info" href="{{ route('admin.donation_category.edit', $donationCategory->id) }}">
                            <x-icons.edit />
                            @lang('Edit')
                        </x-button>

                        <x-button confirmDelete class="btn-sm btn-danger"
                            href="{{ route('admin.donation_category.delete', $donationCategory->id) }}">
                            <x-icons.delete-v2 />
                            @lang('Delete')
                        </x-button>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="100%" class="text-center text-muted">@lang('No donation categories found.')</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    @if ($donationCategories->hasPages())
        <div class="mt-3">
            {!! $donationCategories->links() !!}
        </div>
    @endif
@endsection