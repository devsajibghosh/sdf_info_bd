@extends('user.layouts.main')

@section('content')
    <div class="registration-disabled-page">
        <div class="content-wrapper">
            <div class="svg-container">
                {{-- SVG for "Registration Disabled" or "User Off" --}}
                {{-- Example: User with an 'X' or a lock --}}
                <svg class="disabled-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none"
                    stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"></path>
                    <circle cx="9" cy="7" r="4"></circle>
                    <line x1="17" y1="8" x2="22" y2="13"></line>
                    <line x1="22" y1="8" x2="17" y2="13"></line>
                </svg>
                {{-- Alternative SVG - A Shield with an X --}}
                {{-- <svg class="disabled-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path>
                <line x1="10" y1="10" x2="14" y2="14"></line>
                <line x1="14" y1="10" x2="10" y2="14"></line>
            </svg> --}}
            </div>

            <h1 class="title">@lang('Registration Temporarily Unavailable')</h1>
            <p class="message">
                @lang('We are currently not accepting new registrations. This may be due to scheduled maintenance or system updates to enhance your experience.')
            </p>
            <p class="message">
                @lang('Please check back later, or if you have an urgent inquiry, feel free to contact our support team.')
            </p>

            <div class="actions">
                <a href="{{ route('home') }}" class="btn btn-primary">
                    @lang('Go to Homepage')
                </a>
            </div>
        </div>
    </div>
@endsection

@push('styles')
    <style>
        :root {
            --primary-color: #3498db;
            --secondary-color: #95a5a6;
            --text-color: #34495e;
            --light-text-color: #7f8c8d;
            --bg-color: #f4f6f8;
            --card-bg-color: #ffffff;
            --border-color: #e0e0e0;
        }

        .registration-disabled-page {
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 80vh;
            padding: 40px 20px;
            overflow: hidden;
        }

        .content-wrapper {
            background-color: var(--card-bg-color);
            padding: 40px 30px;
            border-radius: 12px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
            text-align: center;
            max-width: 600px;
            width: 100%;
            animation: fadeInScaleUp 0.8s ease-out forwards;
            opacity: 0;
            transform: scale(0.95) translateY(20px);
        }

        @keyframes fadeInScaleUp {
            to {
                opacity: 1;
                transform: scale(1) translateY(0);
            }
        }

        .svg-container {
            margin-bottom: 25px;
            animation: iconPopIn 0.6s 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275) forwards;
            transform: scale(0);
            opacity: 0;
        }

        @keyframes iconPopIn {
            to {
                transform: scale(1);
                opacity: 1;
            }
        }

        .disabled-icon {
            width: 80px;
            height: 80px;
            color: var(--primary-color);
            /* Or a warning color like orange/red */
            animation: iconFloat 3s ease-in-out infinite;
        }

        @keyframes iconFloat {

            0%,
            100% {
                transform: translateY(0);
            }

            50% {
                transform: translateY(-10px);
            }
        }

        .title {
            font-size: 2rem;
            /* 32px */
            font-weight: 700;
            color: var(--text-color);
            margin-bottom: 15px;
            animation: textSlideIn 0.7s 0.5s ease-out forwards;
            opacity: 0;
            transform: translateY(15px);
        }

        .message {
            font-size: 1rem;
            color: var(--light-text-color);
            line-height: 1.6;
            margin-bottom: 20px;
            animation: textSlideIn 0.7s 0.65s ease-out forwards;
            opacity: 0;
            transform: translateY(15px);
        }

        .message:last-of-type {
            margin-bottom: 30px;
        }


        @keyframes textSlideIn {
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .actions {
            display: flex;
            justify-content: center;
            gap: 15px;
            /* Space between buttons */
            animation: buttonsFadeIn 0.7s 0.8s ease-out forwards;
            opacity: 0;
        }

        @keyframes buttonsFadeIn {
            from {
                opacity: 0;
                transform: translateY(10px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* Responsive adjustments */
        @media (max-width: 768px) {
            .content-wrapper {
                padding: 30px 20px;
            }

            .title {
                font-size: 1.75rem;
                /* 28px */
            }

            .disabled-icon {
                width: 70px;
                height: 70px;
            }

            .actions {
                flex-direction: column;
                gap: 10px;
            }

        }
    </style>
@endpush
