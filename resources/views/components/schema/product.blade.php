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
    'reviews' => null,        // Collection من ProductReview المعتمدة (أو null)
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

    // AggregateRating + Review[] بتطلع بس لو فيه تقييمات معتمدة حقيقية (المرحلة 6D).
    // مفيش أي rating مفبرك — المصدر هو جدول product_reviews المعتمد بس.
    $reviewItems = collect($reviews ?? [])->filter(fn ($r) => filled($r->body) && $r->rating >= 1);
    if ($reviewItems->isNotEmpty()) {
        $avg = round($reviewItems->avg('rating'), 1);

        $data['aggregateRating'] = [
            '@type'       => 'AggregateRating',
            'ratingValue' => (string) $avg,
            'reviewCount' => (string) $reviewItems->count(),
            'bestRating'  => '5',
            'worstRating' => '1',
        ];

        $data['review'] = $reviewItems->map(fn ($r) => array_filter([
            '@type'        => 'Review',
            'author'       => ['@type' => 'Person', 'name' => $r->authorName()],
            'reviewRating' => [
                '@type'       => 'Rating',
                'ratingValue' => (string) $r->rating,
                'bestRating'  => '5',
                'worstRating' => '1',
            ],
            'name'          => $r->title ?: null,
            'reviewBody'    => $r->body,
            'datePublished' => optional($r->created_at)->toDateString(),
        ], fn ($v) => $v !== null && $v !== '' && $v !== []))->values()->all();
    }
@endphp
<x-schema.graph :data="$data" />
