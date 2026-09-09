@extends('admin.layouts.app')

@section('content')
    <div class="mb-3 d-flex justify-content-between">
        <h3>@lang('Page List')</h3>

        <div class="d-flex align-items-center gap-2">
            <x-button href="{{ route('admin.website.page.new') }}">
                <x-icons.add />
                @lang('Add New')
            </x-button>
        </div>
    </div>

    <table class="table">
        <thead>
            <th>@lang('Page Title')</th>
            <th>@lang('Privacy')</th>
            <th>@lang('Default')</th>
            <th>@lang('Created At')</th>
            <th class="text-end">@lang('Action')</th>
        </thead>

        @foreach ($pages as $page)
            <tr>
                <td>
                    {{ $page->title }}
                </td>
                <td>{!! $page->privacyPageBadge !!} </td>
                <td>
                    {!! $page->is_default_badge !!}
                </td>
                <td>{{ software()->getDateTime($page->created_at) }}</td>
                <td class="text-end">
                    <x-button class="btn-sm btn-info" href="{{ route('admin.website.page.edit', $page->id) }}">
                        <x-icons.edit />
                        @lang('Edit')
                    </x-button>

                    @unless ($page->is_default)
                        <x-button confirmDelete class="btn-sm btn-danger" href="{{ route('admin.website.page.delete', $page->id) }}">
                            <x-icons.delete-v2  />
                            @lang('Delete')
                        </x-button>
                    @endunless
                </td>
            </tr>
        @endforeach
    </table>

    @if ($pages->hasPages())
        <div class="mt-3">
            {!! $pages->links() !!}
        </div>
    @endif
@endsection
