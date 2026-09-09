@extends('admin.layouts.app')

@section('content')
    <x-page-header 
        :page_title="__('Admin Logins')" 
    >
        <x-admin-search />
    </x-page-header>

    <table class="table">
        <thead>
            <th>@lang('Admin')</th>
            <th>@lang('IP')</th>
            <th>@lang('City')</th>
            <th>@lang('Browser')</th>
            <th>@lang('OS')</th>
            <th>@lang('Device Type')</th>
            <th class="text-end">@lang('Login Time')</th>
        </thead>

        <tbody>
            @forelse ($adminLogins as $adminLogin)
                <tr>
                    <td>
                        <div>
                            <strong>{{ $adminLogin?->admin?->name ?? 'N/A' }}</strong>
                            <br>
                            <small>{{ $adminLogin?->admin?->email ?? 'N/A' }}</small>
                        </div>
                    </td>
                    <td>{{ $adminLogin->ip }}</td>
                    <td>{{ $adminLogin->city }}</td>
                    <td>{{ $adminLogin->browser }}</td>
                    <td>{{ $adminLogin->os }}</td>
                    <td>{{ $adminLogin->device_type }}</td>
                    <td class="text-end">
                        {{ System::getDateTime($adminLogin->created_at) }}
                        <br>
                        <strong>{{ $adminLogin->created_at->diffForHumans() }}</strong>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="100%" class="text-center text-muted">@lang('No data found.')</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <x-admin-paginate :model="$adminLogins" />
@endsection