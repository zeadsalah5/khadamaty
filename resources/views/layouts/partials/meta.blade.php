@inject('seo', 'App\Support\Seo')
@php
    $company   = site('name');
    $pageTitle = $seo->getPageTitle();
    $pageDesc  = $seo->getDescription();
    $canonical = $seo->getCanonical();
    $robots    = $seo->getRobots();
    $ogImage   = $seo->getOgImage();
    $twitter   = trim((string) site('twitter_handle'));
@endphp
<title>{{ $pageTitle }}</title>
<meta name="description" content="{{ $pageDesc }}">
<meta name="robots" content="{{ $robots }}">
<link rel="canonical" href="{{ $canonical }}">

{{-- hreflang (self-referential) --}}
<link rel="alternate" hreflang="ar-eg" href="{{ $canonical }}">
<link rel="alternate" hreflang="x-default" href="{{ $canonical }}">

{{-- Open Graph --}}
<meta property="og:type" content="{{ $seo->getOgType() }}">
<meta property="og:site_name" content="{{ $company }}">
<meta property="og:title" content="{{ $pageTitle }}">
<meta property="og:description" content="{{ $pageDesc }}">
<meta property="og:url" content="{{ $canonical }}">
<meta property="og:locale" content="ar_EG">
<meta property="og:image" content="{{ $ogImage }}">

{{-- Twitter --}}
<meta name="twitter:card" content="summary_large_image">
<meta name="twitter:title" content="{{ $pageTitle }}">
<meta name="twitter:description" content="{{ $pageDesc }}">
<meta name="twitter:image" content="{{ $ogImage }}">
@if ($twitter !== '')
<meta name="twitter:site" content="{{ $twitter }}">
@endif
