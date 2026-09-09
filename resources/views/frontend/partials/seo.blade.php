{{-- Base title (fallback) --}}
<title>{{ $seoContent->meta_title ?? config('app.name') }}</title>

@if (!empty($seoContent))
    @php 
        $seoImage =  ($seoContent?->image ?? null) ? asset('storage/'. $seoContent->image) : null;
    @endphp
    
        <meta property="og:type" content="website">


    {{-- Standard SEO --}}
    @if(!empty($seoContent->meta_description))
        <meta name="description" content="{{ $seoContent->meta_description }}">
    @endif
    @if(!empty($seoContent->meta_keywords))
        <meta name="keywords" content="{{ $seoContent->meta_keywords }}">
    @endif

    {{-- Open Graph (Facebook) --}}
    <meta property="og:type" content="website">
    <meta property="og:site_name" content="{{ config('app.name') }}">
    <meta property="og:url" content="{{ url()->current() }}">
    <meta property="og:title" content="{{ $seoContent->meta_title ?? config('app.name') }}">
    @if(!empty($seoContent->meta_description))
        <meta property="og:description" content="{{ $seoContent->meta_description }}">
        <meta itemprop="description" content="{{ $seoContent->meta_description }}">
    @endif
    @if($seoImage)
        <meta itemprop="image" content="{{ $seoImage }}">
        <meta property="og:image" content="{{ $seoImage }}">
            <meta property="og:image:type" content="image/png">

        <meta property="og:image:secure_url" content="{{ $seoImage }}">  
               <meta property="og:image:width" content="1180">
        <meta property="og:image:height" content="600">
    @endif

    {{-- Twitter (nice to have) --}}
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="{{ $seoContent->meta_title ?? config('app.name') }}">
    @if(!empty($seoContent->meta_description))
        <meta name="twitter:description" content="{{ $seoContent->meta_description }}">
    @endif
    @if($seoImage)
        <meta name="twitter:image" content="{{ $seoImage }}">
    @endif
@endif
