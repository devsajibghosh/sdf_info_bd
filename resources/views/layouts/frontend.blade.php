<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ isset($title) ? $title : '' }}</title>

    <link rel="stylesheet" href="{{ asset('assets/css/bootstrap.min.css') }}">
    <link href="{{ asset('assets/shared/css/jquery.jgrowl.min.css') }}" rel="stylesheet" />

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
        


        
    </style>

</head>

<body>
    

    @include('admin.partials.alerts')

    @include('user.partials.navbar')
    


    <div class="container">
        @yield('content')
    </div>


    <script src="{{ asset('assets/js/jquery.min.js') }}"></script>
    <script src="{{ asset('assets/shared/js/jquery.jgrowl.min.js') }}"></script>
    

    @stack('scripts')

</body>

</html>
