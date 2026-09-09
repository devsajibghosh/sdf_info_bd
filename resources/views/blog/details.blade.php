@extends('frontend.layouts.main')

@push('structured_data')
<script type="application/ld+json">
{!! json_encode([
    '@context' => 'https://schema.org',
    '@type' => 'Article',
    'headline' => $post->seoTitle(),
    'description' => $post->seoDescription(),
    'image' => [$post->seoImageUrl()],
    'author' => [
        '@type' => 'Person',
        'name' => $post->admin->name ?? config('app.name'),
    ],
    'publisher' => [
        '@type' => 'Organization',
        'name' => config('app.name'),
        'logo' => [
            '@type' => 'ImageObject',
            'url' => System::logo(),
        ],
    ],
    'datePublished' => optional($post->created_at)->toIso8601String(),
    'dateModified' => optional($post->updated_at)->toIso8601String(),
    'mainEntityOfPage' => [
        '@type' => 'WebPage',
        '@id' => $post->canonicalUrl(),
    ],
], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}
</script>
@endpush

@push('styles')
<style>
    .post-container {
        max-width: 1000px; /* more wide for professional look */
        margin: auto;
    }

    .post-card {
        border: none;
        border-radius: 10px;
        overflow: hidden;
        box-shadow: 0 6px 20px rgba(0,0,0,0.05);
        background: #fff;
    }

    .post-image img {
        width: 100%;
        height: 450px;
        object-fit: cover;
    }

    .post-body {
        padding: 3rem; /* p-5 equivalent */
    }

    .post-title {
        font-size: 34px;
        font-weight: 700;
        color: #111827;
        margin-bottom: 10px;
    }

    .post-meta {
        font-size: 14px;
        color: #6b7280;
        margin-bottom: 20px;
    }

    .post-content {
        font-size: 17px;
        line-height: 1.9;
        color: #374151;
        text-align: justify;
    }

    .post-content img {
        max-width: 100%;
        border-radius: 8px;
        margin: 20px 0;
    }

    /* Share Section */
    .post-share {
        border-top: 1px solid #eee;
        margin-top: 30px;
        padding-top: 15px;
    }

    .share-icons a {
        font-size: 18px;
        margin-left: 12px;
        transition: 0.3s;
    }

    .share-icons a:hover {
        transform: translateY(-2px);
        opacity: 0.7;
    }
</style>
@endpush

@section('content')
<div class="container py-5">
    <div class="post-container">
        <div class="post-card">

            {{-- Image --}}
            <div class="post-image">
                <img src="{{ $post->image ? asset('storage/' . $post->image) : asset('no-image.png') }}" alt="{{ $post->seoImageAlt() }}" width="1200" height="450" onerror="this.onerror=null;this.src='{{ asset('no-image.png') }}';">
            </div>

            <div class="post-body">

                {{-- Title --}}
                <h1 class="post-title">{{ $post->title }}</h1>

                {{-- Meta --}}
                <div class="post-meta">
                    <i class="far fa-calendar-alt"></i>
                    {{ System::getDateTime($post->created_at) }}
                    &nbsp; | &nbsp;
                    <i class="far fa-user"></i>
                    {{ $post->admin->name ?? 'Admin' }}
                </div>

                {{-- Content --}}
                <div class="post-content">
                    {!! $post->body !!}
                </div>

                {{-- 🔥 Share Under Content --}}
                @php
                    $url = urlencode(request()->fullUrl());
                    $title = urlencode($post->title);
                @endphp

                <div class="post-share d-flex justify-content-between align-items-center">
                    <span class="text-muted">Share this post</span>

                    <div class="share-icons">
                        <a href="https://www.facebook.com/sharer/sharer.php?u={{ $url }}" target="_blank" class="text-primary">
                            <i class="fab fa-facebook-f"></i>
                        </a>

                        <a href="https://twitter.com/intent/tweet?url={{ $url }}&text={{ $title }}" target="_blank" class="text-info">
                            <i class="fab fa-twitter"></i>
                        </a>

                        <a href="https://api.whatsapp.com/send?text={{ $title }}%20{{ $url }}" target="_blank" class="text-success">
                            <i class="fab fa-whatsapp"></i>
                        </a>

                        <a href="https://www.linkedin.com/sharing/share-offsite/?url={{ $url }}" target="_blank" class="text-primary">
                            <i class="fab fa-linkedin"></i>
                        </a>
                    </div>
                </div>

            </div>

        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    $(document).ready(function () {
        $('.post-content table').addClass('table table-bordered');
    });
</script>
@endpush