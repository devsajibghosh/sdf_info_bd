@extends('frontend.layouts.main')

@php
    $value = \App\Models\Setting::where('key', 'section_video_content')->first()?->value;

    $videos = $value['items'];

    function getEmbedUrl($url)
    {
        if (str_contains($url, 'youtu.be/')) {
            $videoId = basename($url);
            return "https://www.youtube.com/embed/$videoId";
        }

        if (str_contains($url, 'youtube.com/watch')) {
            parse_str(parse_url($url, PHP_URL_QUERY), $query);
            return 'https://www.youtube.com/embed/' . ($query['v'] ?? '');
        }

        return $url;
    }

@endphp

@section('content')
    <div class="video-section py-60">
        <div class="container">
            <div class="row">
                <div class="section-heading">
                    <h2 class="section-heading__title">{{ __($value['heading']) }}</h2>
                    <p class="section-heading__desc">
                        {{ __($value['subheading']) }}
                    </p>
                </div>
            </div>
            <div class="row gy-4 justity-conetent-center">

                @foreach ($videos as $video)
                    <div class="col-lg-4 col-sm-6">
                        <div class="video-item">
                            <div class="video-item__box">
                                <iframe src="{{ getEmbedUrl($video['url']) }}" title="YouTube video player" frameborder="0"
                                    allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share"
                                    referrerpolicy="strict-origin-when-cross-origin" allowfullscreen></iframe>
                            </div>
                            <h5 class="video-item__title">
                                {{ __($video['title']) }}
                            </h5>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
@endsection