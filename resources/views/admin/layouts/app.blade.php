<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <link rel="icon" href="{{ System::favicon() }}">

    <title>@lang('Admin') - {{ isset($title) ? $title : '' }}</title>

    <!-- CSS libraries-->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/jquery-minicolors/2.3.6/jquery.minicolors.css" />
    <link href="{{ asset('assets/shared/css/bootstrap.min.css') }}" rel="stylesheet">
    <link href="{{ asset('assets/css/font-awesome/css/font-awesome.min.css') }}" rel="stylesheet">
    <link href="{{ asset('assets/admin/css/dataTables.bootstrap5.min.css') }}" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('assets/shared/css/jquery.jgrowl.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/select2.min.css') }}" />
    <link rel="stylesheet" href="{{ asset('assets/admin/css/style.css') }}">

    <link href="https://fonts.googleapis.com/css?family=Roboto:300,400,500,700" rel="stylesheet">
    
 {{-- fontawsome cdn --}}

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.0.1/css/all.min.css" integrity="sha512-2SwdPD6INVrV/lHTZbO2nodKhrnDdJK9/kg2XD1r9uGqPo1cUbujc+IYdlYdEErWNu69gVcYgdxlmVmzTWnetw==" crossorigin="anonymous" referrerpolicy="no-referrer" />

    @stack('styles')
</head>

<body class="">

    @include('admin.partials.alerts')

    <div id="wrapper">
        <div id="page-content-wrapper">
            @include('admin.partials.sidebar')

            <button href="#menu-toggle" class="wrapper_toggle_btn" id="menu-toggle">
                <x-icons.menu />
            </button>

            @include('admin.partials.navbar')

            <div class="container-fluid">
                <div class="content-area">
                    @yield('content')
                </div>
            </div>

        </div>
    </div>

    <!-- Scripts -->
    <script src="{{ asset('assets/js/jquery.min.js') }}"></script>
    <script src="{{ asset('assets/shared/js/jquery.jgrowl.min.js') }}"></script>
    <script src="{{ asset('assets/js/bootstrap.bundle.min.js') }}"></script>
    <script src="{{ asset('assets/js/select2.min.js') }}"></script>
    <script src="{{ asset('assets/admin/js/jquery.dataTables.min.js') }}"></script>
    <script src="{{ asset('assets/admin/js/dataTables.bootstrap5.min.js') }}"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-minicolors/2.3.6/jquery.minicolors.min.js"></script>
    <script src="{{ asset('assets/admin/js/software.js') }}"></script>
    <script>
        // Bridges a few user-facing JS strings into the current locale, following
        // the same lang/{locale}.json convention used by the __() helper
        // server-side (see SetLocale middleware). Add keys here only for
        // strings a script needs to show without a round-trip to the server.
        window.appTrans = {
            unknownError: @json(__('An unknown error occurred.')),
        };
    </script>
    <script src="{{ asset('assets/admin/js/SystemHelper.js') }}"></script>
    <script src="{{ asset('assets/shared/js/Sortable.min.js') }}"></script>

    <!-- Optional: AlpineJS from CDN (for Livewire interactivity) -->
    {{-- <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script> <!-- ✅ Alpine --> --}}

    <!-- Livewire Scripts -->
    {{-- @livewireScripts  --}}

    {{-- @isset($js)
    {!! $js !!}
    @endisset --}}

    @stack('scripts')
</body>

</html>
