@props([
    'name',
    'description' => null,
    'url' => null,
    'sku' => null,
    'image' => null,          // string أو array من روابط مطلقة
    'brand' => null,
    'price' => null,          // null أو 0 => مفيش offers
    'currency' => 'EGP',
    'condition' => null,      // https://schema.org/NewCondition | UsedCondition
    'availability' => null,   // https://schema.org/InStock | OutOfStock | SoldOut
])
@php
    $hasPrice = $price !== null && (float) $price > 0;
    $images = is_array($image) ? array_values(array_filter($image)) : ($image ? [$image] : []);

    $data = array_filter([
        '@context'    => 'https://schema.org',
        '@type'       => 'Product',
        'name'        => $name,
        'description' => $description,
        'url'         => $url,
        'sku'         => $sku,
        'image'       => $images ?: null,
        'brand'       => $brand ? ['@type' => 'Brand', 'name' => $brand] : null,
    ], fn ($v) => $v !== null);

    // offers بتتضاف بس لو فيه سعر حقيقي من الداتابيز (مفيش سعر placeholder).
    if ($hasPrice) {
        $data['offers'] = array_filter([
            '@type'         => 'Offer',
            'price'         => (string) $price,
            'priceCurrency' => $currency,
            'url'           => $url,
            'availability'  => $availability ?: 'https://schema.org/InStock',
            'itemCondition' => $condition,
        ], fn ($v) => $v !== null);
    }
    // ملاحظة: مفيش AggregateRating/Review نهائياً.
@endphp
<x-schema.graph :data="$data" />
