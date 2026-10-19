@props([
    'headline',
    'description' => null,
    'url' => null,
    'image' => null,
    'datePublished' => null,
    'dateModified' => null,
])
@php
    $data = [
        '@context'      => 'https://schema.org',
        '@type'         => 'Article',
        'headline'      => $headline,
        'description'   => $description,
        'url'           => $url,
        'image'         => $image,
        'datePublished' => $datePublished,
        'dateModified'  => $dateModified ?: $datePublished,
        'inLanguage'    => 'ar-EG',
        'author'        => [
            '@type' => 'Organization',
            'name'  => site('name'),
        ],
        'publisher'     => [
            '@type' => 'Organization',
            'name'  => site('name'),
        ],
    ];
@endphp
<x-schema.graph :data="$data" />
