@extends('frontend.layouts.main')

@section('content')
    @include('frontend.sections.donation')

    @foreach (\App\Models\Page::where('slug', 'home')->first()->sections as $section)
        @include('frontend.sections.' . $section)
    @endforeach

    @include('frontend.sections.blog')
    @include('frontend.sections.donation-category')

    @include('frontend.sections.projects')

    @include('frontend.sections.gallery')
    
    
    {{-- Floating WhatsApp button: opens a chat with the site phone (General Setting > Site Phone)
         with the opening question already typed in. --}}
    @php
        $whatsappNumber = preg_replace('/\D/', '', (string) generalSetting('site_phone'));
        if (str_starts_with($whatsappNumber, '0')) {
            $whatsappNumber = '88' . $whatsappNumber; // local BD number -> international format
        }
    @endphp
    @if ($whatsappNumber)
        <a href="https://wa.me/{{ $whatsappNumber }}?text={{ rawurlencode('Apni SDF er kon bishoy e jante chan ?') }}"
            target="_blank" rel="noopener" class="whatsapp-float" aria-label="@lang('Chat with SDF on WhatsApp')">
            <span class="whatsapp-float__label">@lang('May I help you?')👋</span>
            <span class="whatsapp-float__btn">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 32 32" width="32" height="32" fill="#fff" aria-hidden="true">
                    <path d="M16.004 3C8.82 3 3 8.82 3 16c0 2.293.6 4.533 1.74 6.507L3 29l6.66-1.713A12.94 12.94 0 0 0 16.004 29C23.18 29 29 23.18 29 16S23.18 3 16.004 3zm0 23.627c-2.013 0-3.987-.54-5.707-1.56l-.407-.24-3.953 1.013 1.053-3.853-.267-.42A10.6 10.6 0 0 1 5.373 16c0-5.86 4.773-10.627 10.634-10.627 5.86 0 10.626 4.767 10.626 10.627 0 5.86-4.766 10.627-10.63 10.627zm5.827-7.96c-.32-.16-1.893-.933-2.187-1.04-.293-.107-.507-.16-.72.16-.213.32-.827 1.04-1.013 1.253-.187.213-.373.24-.693.08-.32-.16-1.353-.5-2.573-1.587-.953-.847-1.593-1.893-1.78-2.213-.187-.32-.02-.493.14-.653.143-.143.32-.373.48-.56.16-.187.213-.32.32-.533.107-.213.053-.4-.027-.56-.08-.16-.72-1.733-.987-2.373-.26-.62-.52-.533-.72-.547l-.613-.013c-.213 0-.56.08-.853.4-.293.32-1.12 1.093-1.12 2.667 0 1.573 1.147 3.093 1.307 3.307.16.213 2.253 3.44 5.46 4.827.763.33 1.36.527 1.823.673.767.244 1.464.21 2.016.127.615-.092 1.893-.773 2.16-1.52.267-.747.267-1.387.187-1.52-.08-.133-.293-.213-.613-.373z"/>
                </svg>
            </span>
        </a>
    @endif

@endsection


@push('styles')

<link rel="stylesheet" href="{{ asset('assets/frontend/css/motion.css') }}">

<style>
    .sub-title { background: linear-gradient(90deg, #ffffff, #80ffaa); -webkit-background-clip: text; -webkit-text-fill-color: transparent; font-weight: 800; text-transform: uppercase; font-size: 1rem; }
    .modern-title { background: linear-gradient(to right, #ffffff, #e0e0e0); -webkit-background-clip: text; -webkit-text-fill-color: transparent; }


    body { font-family: 'Inter', 'Hind Siliguri', sans-serif; overflow-x: hidden; margin: 0; padding: 0; }

    /* Floating WhatsApp button */
    .whatsapp-float {
        position: fixed;
        right: 24px;
        bottom: 24px;
        z-index: 999;
        display: flex;
        align-items: center;
        gap: 12px;
        text-decoration: none;
    }
    .whatsapp-float__label {
        background: #fff;
        color: #334155;
        border: 1px solid #f1f5f9;
        border-radius: 1rem;
        padding: 8px 16px;
        font-size: 14px;
        font-weight: 500;
        box-shadow: 0 10px 15px -3px rgba(0, 0, 0, .1);
    }
    .whatsapp-float__btn {
        position: relative;
        display: flex;
        align-items: center;
        justify-content: center;
        width: 60px;
        height: 60px;
        border-radius: 50%;
        background: #25D366;
        box-shadow: 0 10px 25px rgba(0, 0, 0, .25);
        transition: background-color .2s, transform .2s;
    }
    .whatsapp-float:hover .whatsapp-float__btn { background: #128C7E; }
    .whatsapp-float:active .whatsapp-float__btn { transform: scale(.92); }
    .whatsapp-float__btn::after {
        content: '';
        position: absolute;
        inset: 0;
        border-radius: 50%;
        background: #25D366;
        z-index: -1;
        animation: whatsapp-pulse 2s cubic-bezier(0.4, 0, 0.6, 1) infinite;
    }
    @keyframes whatsapp-pulse {
        0% { transform: scale(1); opacity: .7; }
        100% { transform: scale(1.8); opacity: 0; }
    }
    @media (max-width: 767px) {
        .whatsapp-float { right: 16px; bottom: 16px; }
        .whatsapp-float__label { display: none; }
    }

</style>
@endpush



@push('scripts')

<script src="{{ asset('assets/frontend/js/motion.js') }}"></script>

<script>
    // এটি যোগ করুন যেন আপনার বুটস্ট্র্যাপ ডিজাইন নষ্ট না হয়
    tailwind.config = {
        corePlugins: {
            preflight: false,
        }
    }
</script>


@endpush
