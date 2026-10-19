@props([
    'name',
    'description' => null,
    'url' => null,
    'sku' => null,
    'image' => null,
    'brand' => null,
    'price' => null,          // null أو 0 => مفيش offers
    'currency' => 'EGP',
])
@php
    $hasPrice = $price !== null && (float) $price > 0;

    $data = array_filter([
        '@context'    => 'https://schema.org',
        '@type'       => 'Product',
        'name'        => $name,
        'description' => $description,
        'url'         => $url,
        'sku'         => $sku,
        'image'       => $image,
        'brand'       => $brand ? ['@type' => 'Brand', 'name' => $brand] : null,
    ], fn ($v) => $v !== null);

    // offers بتتضاف بس لو فيه سعر حقيقي (مفيش سعر placeholder).
    if ($hasPrice) {
        $data['offers'] = [
            '@type'         => 'Offer',
            'price'         => (string) $price,
            'priceCurrency' => $currency,
            'url'           => $url,
            'availability'  => 'https://schema.org/InStock',
        ];
    }
    // ملاحظة: مفيش AggregateRating/Review نهائياً.
@endphp
<x-schema.graph :data="$data" />
