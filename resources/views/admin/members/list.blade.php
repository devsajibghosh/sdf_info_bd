@extends('admin.layouts.app')

@section('content')

<x-page-header 
    :page_title="__('Admins')" 
    :add_route="route('admin.member.create')"
    search="true"
/>

<table class="table">
    <thead>
        <th>@lang('Name')</th>
        <th>@lang('Email')</th>
        <th>@lang('Username')</th>
        <th>@lang('Phone Number')</th>
        <th>@lang('Role')</th>
        <th>@lang('Last Login')</th>
        <th class="text-end">@lang('Action')</th>
    </thead>

    <tbody>
        @forelse ($members as $member)
            <tr>
                <td>{{ $member->name }}</td>
                <td>{{ $member->email }}</td>
                <td>{{ $member->username }}</td>
                <td>
                    <a @if($member->phone_number) href="tel:{{ $member->phone_number }}" @endif>{{ $member->phone_number ?? 'N/A' }}</a>
                </td>
             <td><span class="badge bg-warning text-dark">{{ optional($member->role)->name }}</span></td>
                
                
<td>
    <span class="d-block mb-1">{{ System::getDateTime($member->last_login)}}</span>
    
    @if(Carbon\Carbon::parse($member->last_login)->gt(now()->subDays(7)))
        <span class="badge bg-dark text-warning">
        {{ diffForHumans($member->last_login) }}
        </span>
    @else
        <span class="badge bg-secondary">
            {{ diffForHumans($member->last_login)}}
        </span>
    @endif
</td>
                
                
                <td class="text-end">
                    <x-button class="btn-sm btn-info" href="{{ route('admin.member.edit', $member->id) }}">
                        <x-icons.edit />
                        @lang('Edit')
                    </x-button>

                    <x-button confirmDelete class="btn-sm btn-danger" href="{{ route('admin.member.delete', $member->id) }}">
                        <x-icons.delete-v2 />
                        @lang('Delete')
                    </x-button>
                </td>
            </tr>
        @empty
            <x-admin-empty-table />
        @endforelse
    </tbody>
</table>

<x-admin-paginate :model="$members" />

@endsection
