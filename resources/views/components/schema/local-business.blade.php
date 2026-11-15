@inject('seo', 'App\Support\Seo')
@php
    use App\Support\Site;

    $base = rtrim($seo->baseUrl(), '/');

    // المناطق المنشورة فعلاً (اسم كل منطقة) — ولو مفيش، الوصف العام من الإعدادات.
    $areaNames = \App\Support\ContentCache::remember('schema.area_names', fn () => \App\Models\Area::query()
        ->where('is_published', true)->orderBy('name_ar')->pluck('name_ar')->all());
    $areaServed = $areaNames
        ? array_map(fn ($n) => ['@type' => 'City', 'name' => $n], $areaNames)
        : site('areas_label');

    // الحسابات الرسمية من إعدادات الموقع (روابط السوشيال) — بتتضاف بس لو معبّية.
    $sameAs = array_values(array_filter(array_map(
        fn ($l) => is_array($l) ? trim((string) ($l['url'] ?? '')) : '',
        (array) site('social_links', [])
    )));

    // بنطبع بس الحقول اللي ليها قيمة حقيقية (مفيش telephone/address placeholder).
    $data = [
        '@context'    => 'https://schema.org',
        '@type'       => 'HVACBusiness',
        '@id'         => $base . '/#business',
        'name'        => site('name'),
        'description' => site('name') . ' — ' . site('tagline')
                         . ' في ' . site('areas_label') . '.',
        'url'         => $base . '/',
        'areaServed'  => $areaServed,
        'telephone'   => Site::phoneTel(),   // null لو placeholder => بيتشال
        'email'       => Site::email(),       // null لو فاضي => بيتشال
        'sameAs'      => $sameAs,             // فاضية => بتتشال
    ];
    // ملاحظة: مفيش PostalAddress ولا openingHours ولا priceRange ولا AggregateRating
    // لحد ما تكون بيانات حقيقية. TODO لصاحب الموقع.
@endphp
<x-schema.graph :data="$data" />
