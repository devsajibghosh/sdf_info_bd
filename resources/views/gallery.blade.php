@extends('frontend.layouts.main')

@php
    $value = \App\Models\Setting::where('key', 'section_gallery_content')->first()?->value;
    $groupedGalleries = \App\Models\Gallery::with('galleryCategory')
        ->active()
        ->get()
        ->groupBy(fn($item) => $item->galleryCategory->name ?? 'Uncategorized');
@endphp

@section('content')
    <x-breadcrumb title="Gallery" />

    <div class="gallery-section py-60">
        <div class="container">

            <div class="row">
                <div class="col-12 col-md-3 mb-4">
                    <div class="nav flex-column nav-pills gallery-nav" id="category-tab" role="tablist"
                        aria-orientation="vertical">
                        @foreach ($groupedGalleries as $categoryName => $galleries)
                            <button class="nav-link {{ $loop->first ? 'active' : '' }}"
                                id="tab-{{ str()->slug($categoryName) }}-tab" data-bs-toggle="pill"
                                data-bs-target="#tab-{{ str()->slug($categoryName) }}" type="button" role="tab"
                                aria-controls="tab-{{ str()->slug($categoryName) }}"
                                aria-selected="{{ $loop->first ? 'true' : 'false' }}">
                                {{ $categoryName }}
                            </button>
                        @endforeach
                    </div>
                </div>

                <div class="col-12 col-md-9">
                    <div class="tab-content" id="category-tabContent">
                        @foreach ($groupedGalleries as $categoryName => $galleries)
                            <div class="tab-pane fade {{ $loop->first ? 'show active' : '' }}"
                                id="tab-{{ str()->slug($categoryName) }}" role="tabpanel"
                                aria-labelledby="tab-{{ str()->slug($categoryName) }}-tab">
                                <div class="row gy-4 popup-gallery justify-content-center">
                                    @foreach ($galleries as $gallery)
                                        <div class="col-12 col-md-6 col-lg-4">
                                            <a class="popup-item gallery-card"
                                                href="{{ asset('storage/' . $gallery->image) }}"
                                                title="{{ $gallery->title ?? '' }}">
                                                <img src="{{ asset('storage/' . $gallery->image) }}"
                                                    class="img-fluid popup-image" alt="{{ $gallery->title ?? '' }}" />
                                                <span class="icon-preview"><i class="fas fa-eye"></i></span>
                                            </a>
                                        </div>
                                    @endforeach

                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('styles')
    <style>
        /* Overall layout */
        .gallery-section {
            background-color: #f9f9fb;
        }

        /* Section heading */
        .section-heading__title {
            font-weight: 700;
            font-size: 2rem;
            color: #222;
        }

        .section-heading__desc {
            color: #666;
            font-size: 1rem;
            max-width: 600px;
            margin: 0 auto;
        }

        /* Left-side nav */
        .gallery-nav .nav-link {
            background: #fff;
            border: 1px solid #ddd;
            margin-bottom: 10px;
            color: #333;
            text-align: left;
            transition: all 0.3s ease;
            border-radius: 6px;
            font-weight: 500;
            padding: 10px 15px;
        }

        .gallery-nav .nav-link:hover,
        .gallery-nav .nav-link.active {
            background-color: hsl(var(--base));
            color: #fff;
            border-color: hsl(var(--base));
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.08);
        }

        /* Gallery cards */
        .gallery-card {
            display: block;
            position: relative;
            overflow: hidden;
            border-radius: 10px;
            transition: transform 0.3s ease;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.06);
        }

        .gallery-card img {
            width: 100%;
            height: auto;
            border-radius: 10px;
            object-fit: cover;
            transition: transform 0.3s ease;
        }

        .gallery-card .icon-preview {
            position: absolute;
            top: 8px;
            right: 8px;
            background: rgba(0, 0, 0, 0.6);
            color: #fff;
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 0.9rem;
            opacity: 0;
            transition: all 0.3s ease;
        }

        .gallery-card:hover {
            transform: scale(1.02);
        }

        .gallery-card:hover img {
            transform: scale(1.05);
        }

        .gallery-card:hover .icon-preview {
            opacity: 1;
        }

        /* Responsive tweaks */
        @media (max-width: 768px) {
            .gallery-nav .nav-link {
                font-size: 0.9rem;
                padding: 8px 12px;
            }

            .section-heading__title {
                font-size: 1.5rem;
            }
        }
    </style>
@endpush
