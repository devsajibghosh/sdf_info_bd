@extends('admin.layouts.app')

@section('content')
<div>
    <x-page-header 
        :page_title="__('Projects')" 
        :add_route="route('admin.project.create')"
        :search="true"
    />

    <table class="table">
        <thead>
            <th>@lang('Title')</th>
            <th>@lang('Image')</th>
            <th>@lang('Created At')</th>
            <th class="text-end">@lang('Action')</th>
        </thead>

        @forelse ($projects as $project)
            <tr>
                <td>{{ __($project->title)}}</td>
                <td>
                    @if ($project->image)
                        <img src="{{ asset('storage/' . $project->image) }}" alt="{{ $project->title }}" height="40">
                    @else 
                        {{ __('N/A') }}
                    @endif
                </td>
                <td>{{ System::getDateTime($project->created_at) }}</td>
                <td class="text-end">
                    <x-button class="btn-sm btn-info" href="{{ route('admin.project.edit', $project->id) }}">
                        <x-icons.edit />
                        @lang('Edit')
                    </x-button>

                    <x-button confirmDelete class="btn-sm btn-danger" href="{{ route('admin.project.delete', $project->id) }}">
                        <x-icons.delete-v2 />
                        @lang('Delete')
                    </x-button>
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="4" class="text-center text-muted">@lang('No projects found.')</td>
            </tr>
        @endforelse
    </table>

    @if ($projects->hasPages())
        <div class="mt-3">
            {!! $projects->links() !!}
        </div>
    @endif
</div>

@endsection
