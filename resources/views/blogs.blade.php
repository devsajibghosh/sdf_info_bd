@extends('frontend.layouts.main')

@section('content')
    <x-breadcrumb title="News Feed" />

    <div class="container py-5">
        <div class="row gy-3">
            @foreach ($blogPosts as $post)
                <div class="col-12 col-md-6 col-lg-4">
                    <div class="card blog-card">
                        <a href="{{ route('site.blog.details', $post->slug) }}" class="blog-card__thumb">
                            <img src="{{ $post->image ? asset('storage/' . $post->image) : asset('no-image.png') }}" alt="{{ $post->title }}" class="img-fluid" onerror="this.onerror=null;this.src='{{ asset('no-image.png') }}';" />
                        </a>

                        <div class="card-body">
                            <small class="d-inline-block mb-2">{{ System::getDateTime($post->created_at) }}</small>
                            <a class="blog-card__title" href="{{ route('site.blog.details', $post->slug) }}">
                                <h5>{{ $post->title }}</h5>
                            </a>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        @if ($blogPosts->hasPages())
            {!! $blogPosts->links() !!}
        @endif
    </div>
@endsection
