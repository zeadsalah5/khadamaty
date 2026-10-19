<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#ffffff">
    <meta name="color-scheme" content="light">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    {{-- الميتا كلها (title / description / canonical / robots / OG / Twitter / hreflang) --}}
    @include('layouts.partials.meta')

    {{-- أكواد التتبع (GA4 / GTM / Pixel / كود إضافي) — من لوحة التحكم --}}
    @include('layouts.partials.tracking-head')

    {{-- خط Cairo — preconnect + preload للستايل مع display=swap --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="preload" as="style"
          href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;500;600;700;800&display=swap">
    <link rel="stylesheet"
          href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;500;600;700;800&display=swap">

    {{-- بيانات منظمة عامة لكل الصفحات --}}
    <x-schema.organization />
    <x-schema.local-business />
    <x-schema.website />

    {{-- schema إضافي خاص بالصفحة --}}
    @stack('schema')

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen flex flex-col">

    @php $gtmId = trim((string) site('gtm_id')); @endphp
    @if ($gtmId !== '')
        <!-- Google Tag Manager (noscript) -->
        <noscript><iframe src="https://www.googletagmanager.com/ns.html?id={{ $gtmId }}" height="0" width="0" style="display:none;visibility:hidden"></iframe></noscript>
        <!-- End Google Tag Manager (noscript) -->
    @endif

    {{-- تخطّي للمحتوى (لمستخدمي لوحة المفاتيح وقارئ الشاشة) --}}
    <a href="#main"
       class="sr-only focus:not-sr-only focus:fixed focus:z-[60] focus:top-3 focus:start-3 focus:px-4 focus:py-2 focus:rounded-full focus:bg-navy focus:text-white focus:font-bold">
        تخطّي للمحتوى
    </a>

    {{-- شريط الإعلان (من لوحة التحكم) --}}
    @if (site('announcement_enabled'))
        <div class="bg-navy text-white text-center text-sm py-2 px-4">
            <span class="font-semibold">{{ site('announcement_text') }}</span>
            @if (filled(site('announcement_link')))
                <a href="{{ site('announcement_link') }}" class="underline underline-offset-4 hover:text-accent transition-colors">{{ site('announcement_link_label') }}</a>
            @endif
        </div>
    @endif

    <x-site.header />

    <main id="main" class="flex-1">
        {{ $slot ?? '' }}
        @yield('content')
    </main>

    <x-site.footer />

    {{-- أزرار عائمة دائرية (احجز / اتصال / واتساب) --}}
    <div class="fixed z-40 bottom-5 start-4 md:bottom-6 md:start-6 flex flex-col gap-3">
        {{-- احجز فني --}}
        <a href="#booking" aria-label="احجز فني"
           class="group relative w-14 h-14 rounded-full bg-[#F97316] hover:bg-[#ea6a0c] text-white flex items-center justify-center shadow-lg shadow-black/20 transition-colors">
            <svg viewBox="0 0 24 24" class="w-7 h-7" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                <rect x="3" y="5" width="18" height="16" rx="2"/><path d="M8 3v4M16 3v4M3 10h18M9 15l2 2 4-4"/>
            </svg>
            <span class="pointer-events-none absolute end-full me-3 top-1/2 -translate-y-1/2 whitespace-nowrap rounded-lg bg-navy text-white text-xs font-semibold px-2.5 py-1 opacity-0 group-hover:opacity-100 transition-opacity hidden md:block">احجز فني</span>
        </a>

        {{-- اتصال --}}
        <a href="tel:{{ site('phone_tel') }}" aria-label="اتصل بينا"
           class="group relative w-14 h-14 rounded-full bg-[#2563EB] hover:bg-[#1d4fd0] text-white flex items-center justify-center shadow-lg shadow-black/20 transition-colors">
            <svg viewBox="0 0 24 24" class="w-7 h-7" fill="currentColor" aria-hidden="true"><path d="M6.6 10.8a15.5 15.5 0 0 0 6.6 6.6l2.2-2.2a1 1 0 0 1 1-.25c1.1.37 2.3.57 3.6.57a1 1 0 0 1 1 1V20a1 1 0 0 1-1 1A17 17 0 0 1 3 4a1 1 0 0 1 1-1h3.5a1 1 0 0 1 1 1c0 1.3.2 2.5.57 3.6a1 1 0 0 1-.25 1l-2.2 2.2Z"/></svg>
            <span class="pointer-events-none absolute end-full me-3 top-1/2 -translate-y-1/2 whitespace-nowrap rounded-lg bg-navy text-white text-xs font-semibold px-2.5 py-1 opacity-0 group-hover:opacity-100 transition-opacity hidden md:block">اتصل بينا</span>
        </a>

        {{-- واتساب --}}
        <a href="https://wa.me/{{ site('whatsapp') }}" target="_blank" rel="noopener" aria-label="تواصل معنا على واتساب"
           class="group relative w-14 h-14 rounded-full bg-accent hover:bg-accent-600 text-white flex items-center justify-center shadow-lg shadow-black/20 transition-colors">
            <svg viewBox="0 0 24 24" class="w-7 h-7" fill="currentColor" aria-hidden="true">
                <path d="M12 2a9.9 9.9 0 0 0-8.5 15l-1.3 4.8 4.9-1.3A10 10 0 1 0 12 2Zm5.8 14.2c-.2.7-1.4 1.3-2 1.4-.5.1-1.2.1-1.9-.1-.4-.1-1-.3-1.8-.6-3-1.3-5-4.4-5.1-4.6-.2-.2-1.3-1.7-1.3-3.2S6.5 7 6.8 6.7a1 1 0 0 1 .7-.3h.5c.2 0 .4 0 .6.5l.8 1.9c.1.2.1.4 0 .6l-.4.5c-.2.2-.3.4-.1.7.2.3.9 1.4 1.9 2.3 1.3 1.1 2.3 1.5 2.6 1.6.3.1.5.1.6-.1l.7-.8c.2-.3.4-.2.7-.1l1.9.9c.3.1.5.2.6.3.1.3.1.6-.1 1.3Z"/>
            </svg>
            <span class="pointer-events-none absolute end-full me-3 top-1/2 -translate-y-1/2 whitespace-nowrap rounded-lg bg-navy text-white text-xs font-semibold px-2.5 py-1 opacity-0 group-hover:opacity-100 transition-opacity hidden md:block">واتساب</span>
        </a>
    </div>
</body>
</html>
