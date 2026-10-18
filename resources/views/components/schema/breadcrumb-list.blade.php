@props(['items' => []])
@php
    // items: [['label' => '...', 'url' => 'https://...'], ...]
    $elements = [];
    foreach (array_values($items) as $i => $item) {
        $elements[] = array_filter([
            '@type'    => 'ListItem',
            'position' => $i + 1,
            'name'     => $item['label'] ?? null,
            'item'     => $item['url'] ?? null,
        ], fn ($v) => $v !== null && $v !== '');
    }
    $data = [
        '@context'        => 'https://schema.org',
        '@type'           => 'BreadcrumbList',
        'itemListElement' => $elements,
    ];
@endphp
<x-schema.graph :data="$data" />
