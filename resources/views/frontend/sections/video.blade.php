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

            @foreach ($videos as $index => $video)
                <div class="col-lg-4 col-sm-6">
                    <div class="video-item">
                        <div class="video-item__box">
                            <iframe
                                id="ytplayer-{{ $index }}"
                                data-yt-index="{{ $index }}"
                                src="{{ getEmbedUrl($video['url']) }}?enablejsapi=1"
                                title="YouTube video player"
                                frameborder="0"
                                allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share"
                                allowfullscreen>
                            </iframe>
                        </div>
                        <h5 class="video-item__title">
                            {{ __($video['title']) }}
                        </h5>
                    </div>
                </div>
            @endforeach


            <div class="col-12 text-center">
                <a href="{{ route('site.videos') }}" class="btn btn--base">@lang('See More')</a>
            </div>
        </div>
    </div>
</div>

@push('scripts')
    <script>
    let players = [];

    function onYouTubeIframeAPIReady() {
        document.querySelectorAll('iframe[id^="ytplayer-"]').forEach((iframe, index) => {
            players[index] = new YT.Player(iframe.id, {
                events: {
                    'onStateChange': onPlayerStateChange
                }
            });
        });
    }

    function onPlayerStateChange(event) {
        if (event.data === YT.PlayerState.PLAYING) {
            players.forEach((player, i) => {
                if (player !== event.target && player.getPlayerState() === YT.PlayerState.PLAYING) {
                    player.pauseVideo();
                }
            });
        }
    }
</script>

@endpush