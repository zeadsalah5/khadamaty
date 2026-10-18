@inject('seo', 'App\Support\Seo')
@php
    $base = rtrim($seo->baseUrl(), '/');
    $data = [
        '@context'    => 'https://schema.org',
        '@type'       => 'Organization',
        'name'        => config('site.name'),
        'url'         => $base . '/',
        'description' => config('site.tagline'),
    ];
@endphp
<x-schema.graph :data="$data" />
