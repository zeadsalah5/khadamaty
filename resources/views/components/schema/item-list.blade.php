@props(['items' => [], 'name' => null])
@php
    // items: [['name' => '...', 'url' => '...'], ...]
    $elements = [];
    foreach (array_values($items) as $i => $item) {
        $elements[] = array_filter([
            '@type'    => 'ListItem',
            'position' => $i + 1,
            'name'     => $item['name'] ?? null,
            'url'      => $item['url'] ?? null,
        ], fn ($v) => $v !== null && $v !== '');
    }
    $data = $elements ? array_filter([
        '@context'        => 'https://schema.org',
        '@type'           => 'ItemList',
        'name'            => $name,
        'itemListElement' => $elements,
    ], fn ($v) => $v !== null && $v !== '') : [];
@endphp
<x-schema.graph :data="$data" />
