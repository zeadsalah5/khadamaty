@props(['reviews' => null, 'url' => null])
@inject('seo', 'App\Support\Seo')
@php
    /*
     | بيطبع تقييمات حقيقية معتمدة فقط (AggregateRating + Review[]) مربوطة
     | بنشاط الشركة. لو مفيش تقييمات => مبيطلعش أي schema (array فاضية).
     | متحطّش هنا أي تقييمات مفبركة — المصدر هو جدول reviews المعتمد بس.
     */
    $items = collect($reviews ?? [])->filter(fn ($r) => filled($r->body) && $r->rating >= 1);

    $data = [];
    if ($items->isNotEmpty()) {
        $base = rtrim($seo->baseUrl(), '/');
        $avg = round($items->avg('rating'), 1);

        $reviewNodes = $items->map(fn ($r) => array_filter([
            '@type'        => 'Review',
            'author'       => ['@type' => 'Person', 'name' => $r->name],
            'reviewRating' => [
                '@type'       => 'Rating',
                'ratingValue' => (string) $r->rating,
                'bestRating'  => '5',
                'worstRating' => '1',
            ],
            'reviewBody'    => $r->body,
            'datePublished' => optional($r->created_at)->toDateString(),
        ], fn ($v) => $v !== null && $v !== '' && $v !== []))->values()->all();

        $data = [
            '@context'        => 'https://schema.org',
            '@type'           => 'HVACBusiness',
            'name'            => config('site.name'),
            'url'             => $url ?: $base . '/',
            'aggregateRating' => [
                '@type'       => 'AggregateRating',
                'ratingValue' => (string) $avg,
                'reviewCount' => (string) $items->count(),
                'bestRating'  => '5',
                'worstRating' => '1',
            ],
            'review' => $reviewNodes,
        ];
    }
@endphp
<x-schema.graph :data="$data" />
