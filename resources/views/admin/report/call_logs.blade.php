@extends('admin.layouts.app')

@section('content')
    <x-page-header 
        :page_title="__('Call Logs')" 
    >
        <x-admin-search />
    </x-page-header>

    <table class="table">
        <thead>
            <th>@lang('Called By')</th>
            <th>@lang('User')</th>
            <th>@lang('Called At')</th>
            <th>@lang('Status')</th>
            <th>@lang('Amount')</th>
            <th>@lang('Approx Date')</th>
            <th>@lang('Next Call Date')</th>
            <th>@lang('Note')</th>
            <th  class="text-end">@lang('Action')</th>
        </thead>

        <tbody>
            @forelse ($callLogs as $callLog)
                <tr>
                    <td>{{ $callLog?->admin?->name ?? '-' }}</td>
                    <td>
                        @if($callLog->user)
                            <a href="{{ route('admin.user.details', $callLog->user->id) }}">
                                <span>{{ $callLog->user->name }}</span>
                            </a>
                            <br />
                            <strong><small>{{ $callLog->user->phone_number }}</small></strong>
                        @endif
                    </td>
                    <td>{{ System::getDateTime($callLog->call_time) }}</td>
                    <td>@php echo $callLog->statusBadge; @endphp</td>
                    <td>{{ $callLog->amount ? System::amountWithCurrency($callLog->amount) : 'N/A' }}</td>
                    <td>{{$callLog->approx_date ?? 'N/A'}}</td>
                    <td>{{$callLog->next_call_date ?? 'N/A'}}</td>
                    <td>{{$callLog->note ?? 'N/A'}}</td>
                    <td class="text-end">
                       <x-button confirmDelete class="btn-danger" href="{{ route('admin.call_log.delete', $callLog->id) }}">
                           <x-icons.delete-v2 />
                           @lang('Delete')</x-button> 
                    </td>
                </tr>    
            @empty
                <x-admin-empty-table />
            @endforelse
        </tbody>
    </table>

    <x-admin-paginate :model="$callLogs" />
@endsection