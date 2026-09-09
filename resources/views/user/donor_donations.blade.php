@extends('user.layouts.main')

@section('content')
<div class="container py-5">
    <h2 class="mb-4">
        @lang('My Donations')
    </h2>

    <div class="table-responsive">
        <table class="table table-bordered table-striped align-middle">
            <thead class="table-light">
                <tr>
                    <th>@lang('Email / Phone Number')</th>
                    <th>@lang('Amount')</th>
                    <th>@lang('Category')</th>
                    <th>@lang('Time of Donation')</th>
                    <th>@lang('Status')</th>
                    <th>@lang('Action')</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($donations as $donation)
                    <tr>
                        <td>
                            <div>
                                {{ $donation->email ?? 'N/A' }} <br>
                                {{ $donation->phone_number ?? 'N/A' }}
                            </div>
                        </td>
                        <td>{{ System::amountWithCurrency($donation->amount) }}</td>
                        <td>{{ $donation->category->name ?? '-' }}</td>
                        <td>{{ System::getDateTime($donation->created_at) }}</td>
                        <td>
                            @php
                                echo $donation->statusBadge;
                            @endphp
                        </td>
                        <td>
                            @if ($donation->status == 1)
                                <a href="{{ route('download.receipt', encrypt($donation->id)) }}" class="btn--sm btn btn--base">@lang('Download')</a>
                            @else
                                {{ '-' }}
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="text-center text-muted">@lang('No donation records found.')</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if ($donations->hasPages())
        <div class="mt-3">
            {!! $donations->links() !!}
        </div>
    @endif
</div>
@endsection
