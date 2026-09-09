@extends('admin.layouts.app')

@section('content')
    <x-page-header 
        :page_title="__('Galleries')" 
        :add_route="route('admin.gallery.create')"
        search="true"
    />

    <table class="table">
        <thead>
            <th>@lang('Title')</th>
            <th>@lang('Category')</th>
            <th>@lang('Image')</th>
            <th>@lang('Status')</th>
            <th>@lang('Created At')</th>
            <th class="text-end">@lang('Action')</th>
        </thead>

        <tbody>
            @forelse ($galleries as $gallery)
                <tr>
                    <td>{{ $gallery->title ?? 'N/A' }}</td>
                    <td>{{ $gallery?->galleryCategory?->name ?? 'N/A' }}</td>
                    <td>
                        @if ($gallery->image)
                            <img src="{{ asset('storage/' . $gallery->image) }}" alt="{{ $gallery->name }}" height="40">
                        @else
                            {{ __('N/A') }}
                        @endif
                    </td>
                    <td>@php echo $gallery->statusBadge; @endphp</td>
                    <td>{{ System::getDateTime($gallery->created_at) }}</td>
                    <td class="text-end">
                        <x-button class="btn-sm btn-info" href="{{ route('admin.gallery.edit', $gallery->id) }}">
                            <x-icons.edit />
                            @lang('Edit')
                        </x-button>

                        <x-button confirmDelete class="btn-sm btn-danger"
                            href="{{ route('admin.gallery.delete', $gallery->id) }}">
                            <x-icons.delete-v2 />
                            @lang('Delete')
                        </x-button>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="100%" class="text-center text-muted">@lang('No gallery items found.')</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    @if ($galleries->hasPages())
        <div class="mt-3">
            {!! $galleries->links() !!}
        </div>
    @endif
@endsection