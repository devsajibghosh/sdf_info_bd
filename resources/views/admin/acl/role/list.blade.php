@extends('admin.layouts.app')

@section('content')

<x-page-header 
    :page_title="__('Roles')" 
    :add_route="route('admin.acl.role.create')"
>

</x-page-header>

<table class="table">
    <thead>
        <th>@lang('Name')</th>
        <th>@lang('Title')</th>
        <th>@lang('Created At')</th>
        <th class="text-end">@lang('Action')</th>
    </thead>

    @forelse ($roles as $role)
        <tr>
            <td>{{ $role->name }}</td>
            <td>{{ $role->title }}</td>
            <td>{{ System::getDateTime($role->created_at) }}</td>
            <td class="text-end">
                <x-button class="btn-sm btn-info" href="{{ route('admin.acl.role.edit', $role->id) }}">
                    <x-icons.edit />
                    @lang('Edit')
                </x-button>

                @if($role->name != 'super-admin')
                    <x-button confirmDelete class="btn-sm btn-danger" href="{{ route('admin.acl.role.delete', $role->id) }}">
                        <x-icons.delete-v2 />
                        @lang('Delete')
                    </x-button>
                @endif
            </td>
        </tr>
    @empty
        <tr>
            <td colspan="4" class="text-center text-muted">@lang('No roles found.')</td>
        </tr>
    @endforelse
</table>

@if ($roles->hasPages())
    <div class="mt-3">
        {!! $roles->links() !!}
    </div>
@endif

@endsection
