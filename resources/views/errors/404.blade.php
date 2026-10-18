@extends('layouts.app')

@php
    // صفحة الخطأ مش بتتأرشف.
    app('App\Support\Seo')->title('الصفحة مش موجودة')->robots('noindex,follow');
@endphp

@section('content')
<section class="mx-auto max-w-3xl px-4 py-20 md:py-28 text-center">
    <p class="text-7xl md:text-8xl font-extrabold text-brand/20 mb-2">404</p>
    <h1 class="text-2xl md:text-3xl font-extrabold mb-3">الصفحة اللي بتدوّر عليها مش موجودة</h1>
    <p class="text-muted text-lg mb-8">يمكن الرابط اتغيّر أو الصفحة اتشالت. جرّب تبدأ من هنا:</p>

    <div class="flex flex-wrap items-center justify-center gap-3 mb-10">
        <a href="{{ url('/') }}"
           class="px-6 py-3 rounded-full bg-brand hover:bg-brand-600 text-white font-bold transition-colors">الرئيسية</a>
        <a href="{{ url('/services') }}"
           class="px-6 py-3 rounded-full border border-line hover:border-brand hover:text-brand font-semibold transition-colors">الخدمات</a>
        <a href="{{ url('/') }}#booking"
           class="px-6 py-3 rounded-full border border-line hover:border-brand hover:text-brand font-semibold transition-colors">احجز فني</a>
        <a href="https://wa.me/{{ config('site.whatsapp') }}" target="_blank" rel="noopener"
           class="px-6 py-3 rounded-full bg-accent hover:bg-accent-600 text-white font-bold transition-colors">واتساب</a>
    </div>

    <nav aria-label="روابط مفيدة" class="text-sm">
        <ul class="flex flex-wrap items-center justify-center gap-x-5 gap-y-2 text-muted">
            <li><a href="{{ url('/areas') }}" class="hover:text-brand">مناطق الخدمة</a></li>
            <li><a href="{{ url('/appliances') }}" class="hover:text-brand">الأجهزة</a></li>
            <li><a href="{{ url('/brands') }}" class="hover:text-brand">الماركات</a></li>
            <li><a href="{{ url('/blog') }}" class="hover:text-brand">المدونة</a></li>
            <li><a href="{{ url('/shop') }}" class="hover:text-brand">المتجر</a></li>
        </ul>
    </nav>
</section>
@endsection
