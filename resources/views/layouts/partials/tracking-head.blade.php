{{--
    أكواد التتبع — بتتحقن في <head> لو متحددة في لوحة التحكم (إعدادات الموقع).
    كلها اختيارية؛ فاضية => مفيش حاجة بتتطبع.
--}}
@php
    $ga4 = trim((string) site('ga4_id'));
    $gtm = trim((string) site('gtm_id'));
    $pixel = trim((string) site('meta_pixel_id'));
    // Google Ads: معرّف الوسم (AW-…) + label تحويل «ضغطة على رقم التليفون» (اختياري).
    $adsId = trim((string) site('google_ads_id'));
    $adsCallLabel = trim((string) site('google_ads_call_label'));
    if (! preg_match('/^AW-\d+$/', $adsId)) { $adsId = ''; }
    if (! preg_match('/^[A-Za-z0-9_-]+$/', $adsCallLabel)) { $adsCallLabel = ''; }
    $customHead = (string) site('custom_head');
@endphp

@if ($gtm !== '')
<!-- Google Tag Manager -->
<script>(function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src='https://www.googletagmanager.com/gtm.js?id='+i+dl;f.parentNode.insertBefore(j,f);})(window,document,'script','dataLayer','{{ $gtm }}');</script>
<!-- End Google Tag Manager -->
@endif

@if ($ga4 !== '' || $adsId !== '')
<script async src="https://www.googletagmanager.com/gtag/js?id={{ $ga4 !== '' ? $ga4 : $adsId }}"></script>
<script>
    window.dataLayer = window.dataLayer || [];
    function gtag(){dataLayer.push(arguments);}
    gtag('js', new Date());
    @if ($ga4 !== '') gtag('config', '{{ $ga4 }}'); @endif
    @if ($adsId !== '') gtag('config', '{{ $adsId }}'); @endif
</script>
@if ($adsId !== '')
<script>
    // ضغطة على أي رابط اتصال (tel:) => تحويل في Google Ads.
    document.addEventListener('click', function (e) {
        var a = e.target.closest && e.target.closest('a[href^="tel:"]');
        if (!a) return;
        @if ($adsCallLabel !== '')
        gtag('event', 'conversion', {send_to: '{{ $adsId }}/{{ $adsCallLabel }}'});
        @else
        gtag('event', 'phone_call_click', {send_to: '{{ $adsId }}'});
        @endif
    }, true);
</script>
@endif
@endif

@if ($pixel !== '')
<script>
    !function(f,b,e,v,n,t,s){if(f.fbq)return;n=f.fbq=function(){n.callMethod?
    n.callMethod.apply(n,arguments):n.queue.push(arguments)};if(!f._fbq)f._fbq=n;
    n.push=n;n.loaded=!0;n.version='2.0';n.queue=[];t=b.createElement(e);t.async=!0;
    t.src=v;s=b.getElementsByTagName(e)[0];s.parentNode.insertBefore(t,s)}(window,document,'script',
    'https://connect.facebook.net/en_US/fbevents.js');
    fbq('init', '{{ $pixel }}'); fbq('track', 'PageView');
</script>
<noscript><img height="1" width="1" style="display:none" src="https://www.facebook.com/tr?id={{ $pixel }}&ev=PageView&noscript=1"/></noscript>
@endif

@if (trim($customHead) !== '')
{{-- كود إضافي خام من لوحة التحكم (أدمن موثوق) --}}
{!! $customHead !!}
@endif
