@extends('admin.layouts.app')

@section('content')
    <x-page-header 
        :page_title="__('Committees')" 
        :add_route="route('admin.committee.create')"
        search="true"
    />

    <table class="table">
        <thead>
            <th>@lang('Name')</th> 
            <th>@lang('Title')</th> 
            <th>@lang('Image')</th>
            <th>@lang('Created At')</th>
            <th>@lang('Last Modifed At')</th>
            <th class="text-end">@lang('Action')</th>
        </thead>

        <tbody>
            @forelse ($committees as $committee)
                <tr>
                    <td>{{ $committee->name }}</td> 
                    <td>{{ $committee->title  ?? '-' }}</td> 
                    <td>
                        @if ($committee->image)
                            <img src="{{ asset('storage/' . $committee->image) }}" alt="{{ $committee->name }}" height="40">
                        @else
                            {{ __('N/A') }}
                        @endif
                    </td>
                    <td>{{ System::getDateTime($committee->created_at) }}</td>
                    <td>{{ System::getDateTime($committee->updated_at) }}</td>
                    <td class="text-end">
                        <x-button class="btn-sm btn-info" href="{{ route('admin.committee.edit', $committee->id) }}">
                            <x-icons.edit />
                            @lang('Edit')
                        </x-button>

                        <x-button confirmDelete class="btn-sm btn-danger"
                            href="{{ route('admin.committee.delete', $committee->id) }}">
                            <x-icons.delete-v2 />
                            @lang('Delete')
                        </x-button>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="100%" class="text-center text-muted">@lang('No committee found.')</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    @if ($committees->hasPages())
        <div class="mt-3">
            {!! $committees->links() !!}
        </div>
    @endif
@endsection