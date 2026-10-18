<?php

/*
|--------------------------------------------------------------------------
| خريطة إعادة التوجيه الدائم (301)
|--------------------------------------------------------------------------
| روابط قديمة => روابط جديدة. بتتطبّق في Route::fallback (شوف routes/web.php).
| - المفتاح: المسار القديم بدون / في الأول ولا في الآخر (مثال: 'old-services').
| - القيمة: المسار الجديد (مثال: '/services').
| أي رابط مش موجود هنا بيروح لصفحة 404 الحقيقية.
|
| TODO: ضيف هنا أي slugs قديمة لما تنقل محتوى من موقع سابق.
*/

return [
    // slugs خدمات المرحلة 1 => صفحات المرحلة 2 (301)
    'services/maintenance'   => '/services/ac-maintenance',
    'services/cleaning'      => '/services/ac-cleaning',
    'services/freon-charge'  => '/services/ac-gas-refill',
    'services/ac-install'    => '/services/ac-installation',
    'services/repair'        => '/services/ac-repair',
    // 'services/move-install' => '/services',  // مفيش بديل مباشر — بيروح 404 طبيعي

    // slugs مناطق قديمة اتشالت من القايمة (المرحلة 3) => هب المناطق (301).
    // مفيش بديل مباشر لكل منطقة، فبنوجّه للهب بدل 404.
    'areas/cairo'      => '/areas',
    'areas/giza'       => '/areas',
    'areas/heliopolis' => '/areas',
    'areas/helwan'     => '/areas',
    'areas/shubra'     => '/areas',

    // 'خدماتنا'        => '/services',
    // 'الاسعار'        => '/#pricing',
];
