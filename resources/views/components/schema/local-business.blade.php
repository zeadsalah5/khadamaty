@inject('seo', 'App\Support\Seo')
@php
    use App\Support\Site;

    $base = rtrim($seo->baseUrl(), '/');

    // بنطبع بس الحقول اللي ليها قيمة حقيقية (مفيش telephone/address placeholder).
    $data = [
        '@context'    => 'https://schema.org',
        '@type'       => 'HVACBusiness',
        'name'        => config('site.name'),
        'description' => config('site.name') . ' — ' . config('site.tagline')
                         . ' في ' . config('site.areas_label') . '.',
        'url'         => $base . '/',
        'areaServed'  => config('site.areas_label'),
        'telephone'   => Site::phoneTel(),   // null لو placeholder => بيتشال
        'email'       => Site::email(),       // null لو فاضي => بيتشال
    ];
    // ملاحظة: مفيش PostalAddress ولا openingHours ولا priceRange ولا AggregateRating
    // لحد ما تكون بيانات حقيقية. TODO لصاحب الموقع.
@endphp
<x-schema.graph :data="$data" />
