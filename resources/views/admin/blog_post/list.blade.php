@extends('admin.layouts.app')

@section('content')
    <x-page-header 
        :page_title="__('Blog Posts')" 
        :add_route="route('admin.blog_post.create')"
        search="true"
    />

    <table class="table">
        <thead>
            <th>@lang('Title')</th>
            <th>@lang('Status')</th>
            <th>@lang('Image')</th>
            <th>@lang('Created At')</th>
            <th>@lang('Last Modifed At')</th>
            <th class="text-end">@lang('Action')</th>
        </thead>

        <tbody>
            @forelse ($blogPosts as $blog)
                <tr>
                    <td>{{ $blog->title }}</td>
                    <td>@php echo $blog->statusBadge; @endphp</td>
                    <td>
                        @if ($blog->image)
                            <img src="{{ asset('storage/' . $blog->image) }}" alt="{{ $blog->title }}" height="40">
                        @else
                            {{ __('N/A') }}
                        @endif
                    </td>
                    <td>{{ System::getDateTime($blog->created_at) }}</td>
                    <td>{{ System::getDateTime($blog->updated_at) }}</td>
                    <td class="text-end">
                        <x-button class="btn-sm btn-info" href="{{ route('admin.blog_post.edit', $blog->id) }}">
                            <x-icons.edit />
                            @lang('Edit')
                        </x-button>

                        <x-button confirmDelete class="btn-sm btn-danger"
                            href="{{ route('admin.blog_post.delete', $blog->id) }}">
                            <x-icons.delete-v2 />
                            @lang('Delete')
                        </x-button>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="4" class="text-center text-muted">@lang('No blog posts found.')</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    @if ($blogPosts->hasPages())
        <div class="mt-3">
            {!! $blogPosts->links() !!}
        </div>
    @endif
@endsection