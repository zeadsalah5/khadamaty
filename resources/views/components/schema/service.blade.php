@props(['name', 'description' => null, 'url' => null, 'areaServed' => null])
@php
    $data = [
        '@context'    => 'https://schema.org',
        '@type'       => 'Service',
        'name'        => $name,
        'description' => $description,
        'url'         => $url,
        'areaServed'  => $areaServed ?: config('site.areas_label'),
        'provider'    => [
            '@type' => 'HVACBusiness',
            'name'  => config('site.name'),
        ],
    ];
@endphp
<x-schema.graph :data="$data" />
