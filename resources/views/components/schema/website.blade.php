@inject('seo', 'App\Support\Seo')
@php
    $base = rtrim($seo->baseUrl(), '/');
    $data = [
        '@context' => 'https://schema.org',
        '@type'    => 'WebSite',
        'name'     => config('site.name'),
        'url'      => $base . '/',
        'inLanguage' => 'ar-EG',
    ];
    // ملاحظة: مفيش SearchAction لأن البحث لسه مش متبني (هيتضاف مع صفحة الـ shop).
@endphp
<x-schema.graph :data="$data" />
