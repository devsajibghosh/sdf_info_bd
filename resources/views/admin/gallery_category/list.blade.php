@extends('admin.layouts.app')

@section('content')
    <x-page-header 
        :page_title="__('Gallery Categories')" 
        :add_route="route('admin.gallery_category.create')"
        search="true"
    />

    <table class="table">
        <thead>
            <th>@lang('Name')</th>
            <th>@lang('Status')</th>
            <th>@lang('Image')</th>
            <th>@lang('Total Images')</th>
            <th>@lang('Created At')</th>
            <th class="text-end">@lang('Action')</th>
        </thead>

        <tbody>
            @forelse ($galleryCategories as $galleryCategory)
                <tr>
                    <td>{{ $galleryCategory->name }}</td>
                    <td>@php echo $galleryCategory->statusBadge; @endphp</td>
                    <td>
                        @if ($galleryCategory->image)
                            <img src="{{ asset('storage/' . $galleryCategory->image) }}" alt="{{ $galleryCategory->name }}" height="40">
                        @else
                            {{ __('N/A') }}
                        @endif
                    </td>
                    <td>{{ $galleryCategory->galleries_count }}</td>
                    <td>{{ System::getDateTime($galleryCategory->created_at) }}</td>
                    <td class="text-end">
                        <x-button class="btn-sm btn-info" href="{{ route('admin.gallery_category.edit', $galleryCategory->id) }}">
                            <x-icons.edit />
                            @lang('Edit')
                        </x-button>

                        <x-button confirmDelete class="btn-sm btn-danger"
                            href="{{ route('admin.gallery_category.delete', $galleryCategory->id) }}">
                            <x-icons.delete-v2 />
                            @lang('Delete')
                        </x-button>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="100%" class="text-center text-muted">@lang('No gallery categories found.')</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    @if ($galleryCategories->hasPages())
        <div class="mt-3">
            {!! $galleryCategories->links() !!}
        </div>
    @endif
@endsection