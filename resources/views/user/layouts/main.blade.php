<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ isset($title) ? $title : '' }}</title>

    <link rel="icon" href="{{ System::favicon() }}">


    @php
        $hex = generalSetting('base_color');
        [$h, $s, $l] = hexToHsl($hex);
    @endphp

    <style>
        :root {
            --base-h: {{ $h }};
            --base-s: {{ $s }};
            --base-l: {{ $l }};
        }
    </style>
    <link rel="stylesheet" href="{{ asset('assets/frontend/css/fontawesome-all.min.css') }}">
    <link href="{{ asset('assets/shared/css/bootstrap.min.css') }}" rel="stylesheet" />
    <link rel="stylesheet" href="{{ asset('assets/frontend/css/main.css') }}?v=1">
    <link rel="stylesheet" href="{{ asset('assets/shared/css/jquery.jgrowl.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/select2.min.css') }}" />



    <style>
        .jGrowl {
            font-family: system-ui, -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, 'Open Sans', 'Helvetica Neue', sans-serif;
            z-index: 9999;
        }

        .jGrowl-notification {
            border: none;
            border-radius: 12px;
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.2);
            padding: 16px 20px;
            font-size: 14px;
            transition: transform 0.3s ease, opacity 0.3s ease;
            color: #fff;
        }

        .jGrowl-notification:hover {
            transform: translateY(-4px);
            box-shadow: 0 12px 24px rgba(0, 0, 0, 0.3);
        }

        .jGrowl-header {
            font-weight: 600;
            margin-bottom: 6px;
            font-size: 16px;
            color: inherit;
        }

        .jGrowl-closer {
            background: transparent;
            color: #fff;
            font-size: 20px;
            right: 10px;
            top: 8px;
            opacity: 0.6;
            transition: opacity 0.2s ease;
        }

        .jGrowl-closer:hover {
            opacity: 1;
        }

        /* Default background (info) */
        .jGrowl-notification {
            background: #2b2b2b;
        }

        /* Success */
        .jgrowl-success {
            background: #28a745 !important;
        }

        /* Error */
        .jgrowl-error {
            background: #dc3545 !important;
        }

        /* Warning */
        .jgrowl-warning {
            background: #ffc107 !important;
            color: #000 !important;
        }

        /* Info */
        .jgrowl-info {
            background: #17a2b8 !important;
        }

        .logo {
            max-width: 100px;
        }

        .select2-container .select2-selection--single {
            height: 37px;
        }

        .select2-container--default .select2-selection--single .select2-selection__rendered {
            line-height: 37px;
        }

        .select2-container--default .select2-selection--single .select2-selection__arrow {
            height: inherit;
        }

        .select2-container--default .select2-selection--single {
            border-color: #dee2e6;
        }

        .select2-container .selection {
            width: 100%;
        }
    </style>

    @stack('styles')

</head>

<body>
    

    @include('admin.partials.alerts')
    @include('user.partials.header_top')

    @include('user.partials.navbar')
    <div class="container">

        @yield('content')
    </div>


    @include('user.partials.footer')


    <script src="{{ asset('assets/js/jquery.min.js') }}"></script>
    <script src="{{ asset('assets/js/select2.min.js') }}"></script>
    <script src="{{ asset('assets/js/bootstrap.bundle.min.js') }}"></script>
    <script src="{{ asset('assets/shared/js/jquery.jgrowl.min.js') }}"></script>

    @stack('scripts')

    <script>
        $(document).ready(function() {
            $('.changeLang').on('change', function() {
                const langKey = $(this).val();
                window.location.href = "{{ route('lang.switch', ':key') }}".replace(':key', langKey);
            });
        });
    </script>
</body>

</html>
