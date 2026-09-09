{{-- =========================
    BANNER SECTION (UNCHANGED)
========================= --}}

@php
    $section = 'banner';
    $value = \App\Models\Setting::where('key', 'section_banner_content')->first();
    $banners = $value?->value['items'] ?? [];
@endphp

<section class="banner-section pb-60 pt-60">
    <div class="container">
        <div class="row">
            <div class="banner-slider">
                @foreach ($banners as $banner)
                    <div class="banner-slider__item">
                        <img src="{{ asset($banner['photo']) }}" alt="@lang('Banner Image')" />
                    </div>
                @endforeach
            </div>
        </div>
    </div>
</section>

{{-- =========================
    STYLES
========================= --}}

@push('styles')
<style>

    /* ===== Banner ===== */
    .banner-section .banner-slider {
        overflow: hidden;
    }

    .banner-section .banner-slider img {
        border-radius: 8px;
        object-fit: contain;
        width: 100%;
    }

    .banner-slider__item {
        max-height: 500px;
        overflow: hidden;
        border-radius: 8px;
    }

    /* A slow, subtle drift on the visible slide only — ambient life, not a showy zoom. */
    @keyframes bannerDrift {
        from { transform: scale(1); }
        to { transform: scale(1.04); }
    }

    .slick-active .banner-slider__item img {
        animation: bannerDrift 6s var(--motion-ease, ease-out) forwards;
    }

    @media (prefers-reduced-motion: reduce) {
        .slick-active .banner-slider__item img {
            animation: none;
        }
    }

</style>
@endpush
