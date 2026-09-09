<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    @php
        // Per-page SEO/social overrides: any view may pass $metaTitle, $metaDescription,
        // $metaImage, $metaImageWidth, $metaImageHeight, $metaCanonical, $metaOgType to
        // override the site-wide defaults below. Pages that pass nothing keep the exact
        // previous global behavior.
        $pageTitle = $metaTitle ?? $site_title;
        $pageDescription = $metaDescription ?? $site_description;
        $pageImage = $metaImage ?? asset('sdf_bn.jpeg');
        $pageImageAlt = $metaImageAlt ?? $pageTitle;
        $pageImageWidth = $metaImageWidth ?? 1200;
        $pageImageHeight = $metaImageHeight ?? 630;
        $pageCanonical = $metaCanonical ?? url()->current();
        $pageOgType = $metaOgType ?? 'website';
        $pageLocale = app()->getLocale() === 'bn' ? 'bn_BD' : 'en_US';
    @endphp

<title>{{ $pageTitle }}</title>

<meta name="title" content="{{ $pageTitle }}">
<meta name="description" content="{{ $pageDescription }}">
<meta name="author" content="{{ config('app.name') }}">
<meta name="robots" content="index,follow,max-image-preview:large,max-snippet:-1,max-video-preview:-1">
<meta name="googlebot" content="index,follow">
<meta name="bingbot" content="index,follow">

<link rel="canonical" href="{{ $pageCanonical }}">

<!-- =========================
     Open Graph (Facebook, WhatsApp, LinkedIn)
========================= -->
<meta property="og:locale" content="{{ $pageLocale }}">
<meta property="og:type" content="{{ $pageOgType }}">
<meta property="og:site_name" content="{{ config('app.name') }}">
<meta property="og:url" content="{{ $pageCanonical }}">
<meta property="og:title" content="{{ $pageTitle }}">
<meta property="og:description" content="{{ $pageDescription }}">
<meta property="og:image" content="{{ $pageImage }}">
<meta property="og:image:secure_url" content="{{ $pageImage }}">
<meta property="og:image:width" content="{{ $pageImageWidth }}">
<meta property="og:image:height" content="{{ $pageImageHeight }}">
<meta property="og:image:alt" content="{{ $pageImageAlt }}">

<!-- =========================
     Twitter Card
========================= -->
<meta name="twitter:card" content="summary_large_image">
<meta name="twitter:url" content="{{ $pageCanonical }}">
<meta name="twitter:title" content="{{ $pageTitle }}">
<meta name="twitter:description" content="{{ $pageDescription }}">
<meta name="twitter:image" content="{{ $pageImage }}">
<meta name="twitter:image:alt" content="{{ $pageImageAlt }}">

<!-- =========================
     Schema.org
========================= -->
<meta itemprop="name" content="{{ $pageTitle }}">
<meta itemprop="description" content="{{ $pageDescription }}">
<meta itemprop="image" content="{{ $pageImage }}">

<!-- =========================
     Mobile & Browser
========================= -->
    
    

    <link rel="shortcut icon" href="{{ System::favicon() }}">

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

    <link rel="stylesheet" href="{{ asset('assets/shared/css/bootstrap.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/frontend/css/fontawesome-all.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/frontend/css/slick.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/frontend/css/line-awesome.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/frontend/css/odometer.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/frontend/css/magnific-popup.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/frontend/css/main.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/shared/css/jquery.jgrowl.min.css') }}" />
    
    {{-- tailwind css area --}}
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
    {{-- Bangla-compatible font: Poppins/Roboto have no Bengali glyphs, so without this
         Bangla text falls back to whatever font the visitor's device happens to have,
         which breaks sizing/line-height and causes the layout to shift when switching
         to the Bangla locale, especially on mobile. --}}
    <link href="https://fonts.googleapis.com/css2?family=Hind+Siliguri:wght@400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    

    <style>

    /* Desktop: menu stays left-aligned and inline */
    @media (min-width: 992px) {
        .navbar-collapse {
            display: flex !important;
            justify-content: flex-start !important;
            visibility: visible !important;
        }
        .navbar-nav {
            margin-left: 0 !important;
            margin-right: auto !important;
        }
    }

    /* Mobile: off-canvas menu that slides in from the left.
       IMPORTANT: this must NOT be keyed off Bootstrap's ".collapse" class —
       Bootstrap's JS briefly REMOVES ".collapse" (swapping in ".collapsing")
       while the open/close transition runs, which used to drop this whole
       ruleset mid-animation and made the panel flash back into normal
       in-flow layout for a moment. Keying off the ID alone keeps the
       fixed/off-canvas box in effect for the entire open+close cycle; only
       the transform (via ".show") animates. */
    @media (max-width: 991px) {
        /* Backdrop: above the header/page, below the sliding sidebar */
        .body-overlay {
            z-index: 1090;
        }

        /* ROOT CAUSE of the sidebar rendering BEHIND the backdrop: main.css gives
           <header id="header"> "position: relative; z-index: 99" at this same
           breakpoint. position+z-index together make <header> its OWN stacking
           context, so #navbarSupportedContent's z-index (however high) is only
           ever compared against header's OTHER children — never against
           .body-overlay, which is a sibling of <header> at the body root. The
           browser instead compares the two stacking contexts as whole units:
           header(99) vs .body-overlay(1090), and header loses, taking the
           sidebar down with it. Neutralizing header's z-index here removes that
           trapped context so the sidebar's z-index is compared directly against
           the backdrop, where 1100 > 1090 wins as intended. */
        .header {
            z-index: auto;
        }

        /* SECOND stacking-context trap, same family of bug as above: <body> is
           "display: flex" (see main.css, used for the sticky-footer layout —
           left untouched here), and .header-middle (a DIRECT flex-item child
           of body) has "z-index: 99" in main.css. Per the flexbox spec,
           z-index applies to flex items EVEN WITHOUT position set, so
           .header-middle silently became its own stacking context capped at
           99 — trapping the hamburger/close button (.header-button, z-index
           1110) inside it. No z-index on the button itself could ever escape
           that cap, so it rendered underneath both .body-overlay (1090) and
           the sliding #navbarSupportedContent panel (1100). Neutralizing it
           here lets the button compare directly at the root level again. */
        .header-middle {
            z-index: auto;
        }

        #navbarSupportedContent {
            display: block !important;
            /* Tailwind's CDN script JIT-scans the DOM for class names and, seeing
               Bootstrap's own ".collapse" class on this element (from
               "collapse navbar-collapse"), generates a colliding Tailwind
               utility: ".collapse { visibility: collapse }". That rule has
               nothing here to override it, so the panel was invisible even
               though it was correctly positioned above the backdrop. Force
               visibility explicitly so this element never inherits it. */
            visibility: visible !important;
            pointer-events: auto !important;
            position: fixed !important;
            top: 0;
            left: 0;
            width: min(85vw, 320px);
            max-width: 320px;
            height: 100vh !important;
            height: 100dvh !important;
            margin: 0 !important;
            /* Top padding must clear the fixed header-middle bar (logo +
               hamburger/close button), which measures ~144px tall and is
               identical at every mobile width from 320px up through the
               991px collapse breakpoint (its height comes from fixed-size
               logo/icon boxes, not viewport units). 76px left the close
               icon (bottom edge ~129px) overlapping the first menu row
               (the language switcher, which started at ~96px). 140px gives
               a clean ~16-20px gap below the header bar at every required
               width (320/375/390/414) with no overlap. */
            padding: 140px 20px 24px;
            background: hsl(var(--white)) !important;
            overflow-y: auto !important;
            overflow-x: hidden;
            -webkit-overflow-scrolling: touch;
            box-sizing: border-box;
            z-index: 1100;
            box-shadow: 6px 0 30px rgba(0, 0, 0, 0.2);
            transform: translateX(-100%);
            transition: transform 0.35s ease;
        }

        #navbarSupportedContent.show {
            transform: translateX(0);
        }

        /* Let the theme's existing .header .nav-item .nav-link rules (flex,
           padding, border, color) style each row — just guarantee full width
           and safe wrapping for long Bangla labels inside the fixed-width panel. */
        #navbarSupportedContent .navbar-nav,
        #navbarSupportedContent .nav-item {
            width: 100%;
        }

        #navbarSupportedContent .nav-link {
            width: 100%;
            overflow-wrap: break-word;
            word-break: break-word;
        }

        #navbarSupportedContent .language-box,
        #navbarSupportedContent .language-box .select {
            width: 100%;
            max-width: 100%;
        }

        /* Keep the toggle/close button clickable above the backdrop and sidebar */
        .header-button.navbar-toggler {
            position: relative;
            z-index: 1110;
        }
    }

    
        .select2 .selection {
            width: 100%;
        }

        /* jgrowl */
        .jGrowl {
            /* font-family: system-ui, -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, 'Open Sans', 'Helvetica Neue', sans-serif; */
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
    
    <script async src="https://pagead2.googlesyndication.com/pagead/js/adsbygoogle.js?client=ca-pub-5297589597986533"
     crossorigin="anonymous"></script>
    
    @stack('styles')
    @stack('structured_data')
</head>

<body class="">

    @include('admin.partials.alerts')
    
    <div class="body-overlay"></div>

    <div class="sidebar-overlay"></div>

    <a class="scroll-top"><i class="fas fa-angle-double-up"></i></a>

    @include('user.partials.header_top')
    @include('user.partials.header_middle')
    @include('user.partials.navbar2')

    @yield('content')

    @include('user.partials.footer')

    <script src="{{ asset('assets/js/jquery.min.js') }}"></script>    
    <script src="{{ asset('assets/js/bootstrap.bundle.min.js') }}"></script>
    <script src="{{ asset('assets/frontend/js/slick.min.js') }}"></script>
    <script src="{{ asset('assets/frontend/js/odometer.min.js') }}"></script>
    <script src="{{ asset('assets/frontend/js/magnific-popup.min.js') }}"></script>
    <script src="{{ asset('assets/frontend/js/viewport.jquery.js') }}"></script>
    <script src="{{ asset('assets/shared/js/jquery.jgrowl.min.js') }}"></script>
    <script src="{{ asset('assets/frontend/js/main.js') }}"></script>

    <script>
        $(document).ready(function (){
            $('.changeLang').on('change', function() {
                const langKey = $(this).val();
                window.location.href = "{{ route('lang.switch', ':key') }}".replace(':key', langKey);
            });

            $('select.select2').each(function() {
                var $this = $(this);

                $this.select2({
                    placeholder: $this.data('placeholder'),
                    minimumResultsForSearch: $this.data('minimum-results-for-search') || 10
                });
            });
        });
        
    
        
    </script>
    
    

    @stack('scripts')
</body>

</html>
