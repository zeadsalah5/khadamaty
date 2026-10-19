@props(['name', 'description' => null, 'url' => null, 'areaServed' => null])
@php
    $data = [
        '@context'    => 'https://schema.org',
        '@type'       => 'Service',
        'name'        => $name,
        'description' => $description,
        'url'         => $url,
        'areaServed'  => $areaServed ?: site('areas_label'),
        'provider'    => [
            '@type' => 'HVACBusiness',
            'name'  => site('name'),
        ],
    ];
@endphp
<x-schema.graph :data="$data" />
