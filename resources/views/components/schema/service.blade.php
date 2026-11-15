@inject('seo', 'App\Support\Seo')
@props(['name', 'description' => null, 'url' => null, 'areaServed' => null, 'serviceType' => null, 'priceNote' => null])
@php
    use App\Support\Site;

    $base = rtrim($seo->baseUrl(), '/');

    // مزوّد الخدمة = نفس كيان النشاط (HVACBusiness) المعرّف في local-business،
    // مربوط بـ @id. telephone بيتضاف بس لو الرقم حقيقي (مش placeholder).
    $provider = [
        '@type' => 'HVACBusiness',
        '@id'   => $base . '/#business',
        'name'  => site('name'),
        'url'   => $base . '/',
    ];
    if (Site::phoneTel()) {
        $provider['telephone'] = Site::phoneTel();
    }

    $data = [
        '@context'    => 'https://schema.org',
        '@type'       => 'Service',
        '@id'         => $url ? $url . '#service' : null,
        'name'        => $name,
        'serviceType' => $serviceType ?: $name,
        'description' => $description,
        'url'         => $url,
        'areaServed'  => $areaServed ?: site('areas_label'),
        'provider'    => $provider,
    ];

    // offers بتتضاف بس لو ملاحظة السعر الظاهرة للزائر فيها رقم واضح (نفس الرقم المعروض).
    if (($price = price_from_note($priceNote)) !== null) {
        $data['offers'] = [
            '@type'         => 'Offer',
            'price'         => $price == (int) $price ? (int) $price : $price,
            'priceCurrency' => 'EGP',
            'description'   => public_note($priceNote),
            'url'           => $url,
        ];
    }
@endphp
<x-schema.graph :data="$data" />
