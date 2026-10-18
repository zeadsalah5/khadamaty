@props(['items' => []])
@php
    // items: [['q' => '...', 'a' => '...'], ...]
    $entities = [];
    foreach ($items as $item) {
        $q = $item['q'] ?? null;
        $a = $item['a'] ?? null;
        if (! $q || ! $a) {
            continue;
        }
        $entities[] = [
            '@type'          => 'Question',
            'name'           => $q,
            'acceptedAnswer' => [
                '@type' => 'Answer',
                'text'  => $a,
            ],
        ];
    }
    $data = $entities ? [
        '@context'   => 'https://schema.org',
        '@type'      => 'FAQPage',
        'mainEntity' => $entities,
    ] : [];
@endphp
<x-schema.graph :data="$data" />
