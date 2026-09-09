@extends('admin.layouts.app')


@section('content')
    <x-page-header 
        :page_title="__('Donations')" 
        search="true"
    />
    

<div class="mb-3 text-end" style="display: flex; justify-content: flex-end; gap: 10px;">
    <!-- PDF Download Button -->
    <x-button 
        href="{{ route('admin.donation.download.monthly') }}" 
        class="btn-primary btn-sm"
        style="display: inline-flex; align-items: center; gap: 6px;"
    >
        <!-- Print / PDF Icon -->
        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16">
            <path d="M2.5 8a.5.5 0 1 0 0-1 .5.5 0 0 0 0 1z"/>
            <path d="M5 1a2 2 0 0 0-2 2v2H2a2 2 0 0 0-2 2v3a2 2 0 0 0 2 2h1v1a2 2 0 0 0 2 2h6a2 2 0 0 0 2-2v-1h1a2 2 0 0 0 2-2V7a2 2 0 0 0-2-2h-1V3a2 2 0 0 0-2-2H5zM4 3a1 1 0 0 1 1-1h6a1 1 0 0 1 1 1v2H4V3zm1 5a2 2 0 0 0-2 2v1H2a1 1 0 0 1-1-1V7a1 1 0 0 1 1-1h12a1 1 0 0 1 1 1v3a1 1 0 0 1-1 1h-1v-1a2 2 0 0 0-2-2H5zm7 2v3a1 1 0 0 1-1 1H5a1 1 0 0 1-1-1v-3a1 1 0 0 1 1-1h6a1 1 0 0 1 1 1z"/>
        </svg>
        <span>{{ __('Download PDF') }}</span>
    </x-button>

    <!-- Excel Download Button -->
    <x-button 
        href="{{ route('admin.donation.download.excel') }}" 
        class="btn-success btn-sm"
        style="display: inline-flex; align-items: center; gap: 6px; background-color: #198754; color: #fff; border-color: #198754;"
    >
        <!-- Excel / Spreadsheet Icon -->
        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16">
            <path d="M5.884 6.68a.5.5 0 1 0-.768.64L7.349 10l-2.233 2.68a.5.5 0 0 0 .768.64L8 10.781l2.116 2.54a.5.5 0 0 0 .768-.64L8.651 10l2.233-2.68a.5.5 0 0 0-.768-.64L8 9.219l-2.116-2.54z"/>
            <path d="M14 14V4.5L9.5 0H4a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h8a2 2 0 0 0 2-2zM9.5 3A1.5 1.5 0 0 0 11 4.5h2V14a1 1 0 0 1-1 1H4a1 1 0 0 1-1-1V2a1 1 0 0 1 1-1h5.5v2z"/>
        </svg>
        <span>{{ __('Download Excel') }}</span>
    </x-button>
</div>




    <table class="table">
        <thead>
            <th>@lang('Phone Number')</th>
            <th>@lang('Name')</th>
            <th>@lang('Transaction No')</th>
            <th>@lang('Channel')</th>
            <th>@lang('Amount')</th>
            <th>@lang('Category')</th>
            <th>@lang('Time of Donation')</th>
            <th>@lang('Status')</th>
            <th>@lang('Field Collect')</th>
            <th>@lang('Info')</th>
            <th>@lang('Approved By')</th>
            <th class="text-end">@lang('Action')</th>
        </thead>

        <tbody>
            
            @forelse ($donations as $donation)
                <tr>
                    <td>
                        <div class="text-start">
                        {{ $donation->phone_number}}
                        </div>
                    </td>
                    <td>
                        
                    {{ $donation->relationwithUser?->name ?? $donation->donor?->name }}
                    
                    </td>
                    <td>{{ $donation?->payment?->transaction_no ?? '-' }}</td>
                    <td>{{ $donation?->payment?->paymentGateway?->name ?? '-' }}</td>
                    <td>{{ System::amountWithCurrency($donation->amount) }}</td>
                    <td>{{ $donation->category->name }}</td>
<td>
<!--<span>{{ ($donation->created_at ?? $donation->user?->created_at) }}</span>-->
<span>{{ System::getDateTime($donation->created_at) }}</span>
<br>
<small class="badge bg-warning">
@if($donation->created_at->gt(now()->subDays(7)))
    {{ $donation->created_at->diffForHumans(null, true, true) }} ago
@else
    {{ $donation->created_at->format('M d, Y') }}
@endif
</small>
</td>
                    <td>
                        @php echo $donation->statusBadge; @endphp
                    </td>
                    <td>
                        @php
                            echo $donation->fieldCollectionBadge
                        @endphp
                    </td>
                    <td>
                        <x-button class="btn-sm metaBtn" data-json="{{ json_encode($donation?->payment?->meta) }}" title="@lang('Click here to see meta information')">@lang('Meta')</x-button>
                    </td>
                    <td>
                        {{ $donation->approvedBy?->name ?? '-' }}
                    </td>
                    <td class="text-end">
                        @if ($donation->status == 0)
                            <x-button confirmDelete class="btn-sm btn-success" title="Approve" href="{{ route('admin.donation.approve', ['id' => $donation->id, 'page' => request()->get('page', 1)]) }}">
                                <x-icons.check-circle />
                            </x-button>
                            <x-button confirmDelete class="btn-sm btn-danger" title="Reject" href="{{ route('admin.donation.reject', ['id' => $donation->id, 'page' => request()->get('page', 1)]) }}">
                                <x-icons.ban />
                            </x-button>
                        @endif
                        
                        <x-button confirmDelete class="btn-sm btn-danger" title="@lang('Delete')"
                            href="{{ route('admin.donation.delete', ['id' => $donation->id, 'page' => request()->page ?? 1]) }}">
                            <x-icons.delete-v2 />
                        </x-button>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="100%" class="text-center text-muted">@lang('No donations found.')</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    @if ($donations->hasPages())
        <div class="mt-3">
            {!! $donations->links() !!}
        </div>
    @endif

    <x-modal 
    id="metaModal" 
    title="{{ __('Donation Meta Information') }}" 
>
        <div class="render"></div>
</x-modal>
@endsection

@push('scripts')
    <script>
        $('.metaBtn').on('click', function() {
            const json = $(this).data('json');
            $('#metaModal').find('.render').html(
                `<pre style="white-space: pre-wrap; word-break: break-word;">${JSON.stringify(json, null, 4)}</pre>`
            );
            $('#metaModal').modal('show');
        });
        
        function updateClock() {
    const now = new Date();

    // Pinned to Bangladesh time so the panel clock stays authoritative
    // regardless of the admin's own device timezone/clock settings.
    // Format the Date: March 15, 2024
    const datePart = now.toLocaleDateString('en-US', {
        month: 'long',
        day: 'numeric',
        year: 'numeric',
        timeZone: 'Asia/Dhaka'
    });

    // Format the Time: 2:30 PM
    const timePart = now.toLocaleTimeString('en-US', {
        hour: 'numeric',
        minute: '2-digit',
        hour12: true,
        timeZone: 'Asia/Dhaka'
    });

    // Combine them to match your format exactly
    const fullString = `${datePart} - ${timePart}`;

    // Update the HTML
    const clockElement = document.getElementById('real-time-clock');
    if (clockElement) {
        clockElement.innerText = fullString;
    }
}

// Update every 1 second (1000ms)
setInterval(updateClock, 1000);

// Run immediately so there's no delay on page load
updateClock();
        
        
    </script>
@endpush