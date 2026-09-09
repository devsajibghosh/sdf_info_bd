@php
    $value = \App\Models\Setting::where('key', 'section_gallery_content')->first()?->value;
    $galleries = \App\Models\Gallery::active()->limit(12)->get(); // আইটেম একটু বেশি হলে অটো-স্ক্রল সুন্দর দেখায়
@endphp

<div class="gallery-section py-60" style="background: #f9f9f9; overflow: hidden;">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-12 col-lg-8">
                <div class="section-heading text-center mb-5 reveal">
                    <h2 class="section-heading__title" style="font-weight: 700; color: #222;">{{ __($value['heading'] ?? 'Our Gallery') }}</h2>
                    <div class="heading-divider mx-auto"></div>
                    <p class="section-heading__desc text-muted mt-3">{{ __($value['subheading'] ?? 'A glimpse into our work and events.') }}</p>
                </div>
            </div>
        </div>
    </div>

    <div class="marquee-wrapper">
        <div class="marquee-content">
            @foreach ($galleries->concat($galleries) as $gallery) {{-- ডাবল করা হয়েছে লুপলেস স্ক্রলিং এর জন্য --}}
                <div class="gallery-card">
                    <a class="popup-item" href="{{ asset('storage/' . $gallery->image) }}">
                        <div class="image-box">
                            <img src="{{ asset('storage/' . $gallery->image) }}" alt="Gallery Image" loading="lazy" width="300" height="220">
                            <div class="overlay">
                                <span class="icon-preview"><i class="fas fa-plus"></i></span>
                            </div>
                        </div>
                    </a>
                </div>
            @endforeach
        </div>
    </div>

    <div class="container mt-5">
        <div class="col-12 text-center">
            <a href="{{ route('site.gallery') }}" class="modern-btn">
                <span>@lang('Explore Full Gallery')</span>
            </a>
        </div>
    </div>
</div>

@push('styles')
<style>
    /* Section Divider */
    .heading-divider {
        width: 60px;
        height: 3px;
        background: linear-gradient(90deg, #ff4b2b, #ff416c);
        border-radius: 5px;
    }

    /* Marquee Layout */
    .marquee-wrapper {
        display: flex;
        width: 100%;
        overflow: hidden;
        user-select: none;
        padding: 20px 0;
    }

    .marquee-content {
        display: flex;
        flex-shrink: 0;
        gap: 20px;
        animation: scroll 30s linear infinite;
    }

    .marquee-wrapper:hover .marquee-content {
        animation-play-state: paused; /* হোভার করলে স্ক্রল থেমে যাবে */
    }

    @keyframes scroll {
        from { transform: translateX(0); }
        to { transform: translateX(-50%); }
    }

    /* Gallery Card Design */
    .gallery-card {
        width: 300px;
        height: 220px;
        flex-shrink: 0;
        border-radius: 15px;
        overflow: hidden;
        box-shadow: 0 10px 20px rgba(0,0,0,0.1);
        position: relative;
        transition: transform 0.3s ease;
    }

    .gallery-card:hover {
        transform: translateY(-5px);
    }

    .image-box {
        width: 100%;
        height: 100%;
        position: relative;
    }

    .image-box img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        transition: transform 0.5s ease;
    }

    .gallery-card:hover img {
        transform: scale(1.1);
    }

    /* Overlay Effect */
    .overlay {
        position: absolute;
        inset: 0;
        background: rgba(0, 0, 0, 0.4);
        display: flex;
        align-items: center;
        justify-content: center;
        opacity: 0;
        transition: opacity 0.3s ease;
    }

    .gallery-card:hover .overlay {
        opacity: 1;
    }

    .icon-preview {
        color: white;
        font-size: 24px;
        background: rgba(255, 255, 255, 0.2);
        width: 50px;
        height: 50px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 50%;
        backdrop-filter: blur(5px);
    }

    /* Modern Button Styles */
    .modern-btn {
        display: inline-block;
        padding: 12px 35px;
        background: #222;
        color: #fff;
        border-radius: 50px;
        text-decoration: none;
        font-weight: 600;
        position: relative;
        overflow: hidden;
        transition: all 0.3s ease;
        border: 1px solid #eee;
    }

    .modern-btn:hover {
        background: #000;
        color: #fff;
        box-shadow: 0 10px 20px rgba(0,0,0,0.15);
    }

    /* Mobile Responsive */
    @media (max-width: 768px) {
        .gallery-card {
            width: 220px;
            height: 160px;
        }
    }
</style>
@endpush