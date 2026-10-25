# مراجعة ما قبل الإطلاق — المتجر والداشبورد (خدماتي)

**التاريخ:** 2026-10-19 · **الفرع:** `feature/filament-admin-phase-c` · **النوع:** مراجعة read-only (مفيش أي تعديل على الكود، ومتقريتش `.env`).

## الخلاصة التنفيذية
البنية الأساسية للمتجر قوية ومدروسة: **كل الأسعار والإجماليات server-side من الداتابيز**، إنشاء الطلب داخل `transaction` مع `lockForUpdate`، الـ webhook بيتحقق من توقيع HMAC وidempotent بقيد فريد على مستوى الداتابيز، صفحة الرجوع مش دليل دفع، ومهلة الدفع + إرجاع المخزون شغّالين ومجدولين. الاختبارات **51 نجحت (189 assertion)** وبتغطّي المسارات الحساسة (المخزون تحت القفل، آخر قطعة مرة واحدة، الـ webhook بأنواعه، noindex).

أهم ما يحتاج قرار قبل الإطلاق:
1. **[حرج]** احتمال XSS مخزّن من متن Markdown (المقالات/الصفحات الثابتة) — الـ HTML الخام مش متأكّد إنه بيتهرّب، والتعليق في الكود بيدّعي العكس.
2. **[مهم]** الـ webhook مابيتحقّقش إن **المبلغ المدفوع = إجمالي الطلب**.
3. **[مهم]** webhook ناجح متأخّر على طلب **اتلغى بالمهلة** بيعلّمه «مدفوع» بعد ما المخزون رجع.
4. **[مهم]** **اختطاف طلبات الضيف** عبر التسجيل برقم موبايل الغير (مفيش تحقق OTP).
5. **[مهم]** **المحرّر (editor)** بيوصل لـ **الطلبات** وفيها بيانات العملاء الشخصية.

---

## حرج

### C1 — احتمال XSS مخزّن من متن الـ Markdown (المقالات والصفحات الثابتة)
- **الملف:** [app/Support/ArticleContent.php:34](app/Support/ArticleContent.php:34) — `$html = Str::markdown($markdown);`
- **العرض الخام:** [resources/views/pages/post.blade.php:100](resources/views/pages/post.blade.php:100) و [resources/views/pages/static-page.blade.php:14](resources/views/pages/static-page.blade.php:14) — `{!! $content->html !!}`
- **المشكلة:** `Str::markdown()` بتتنادى **من غير options**. الافتراضي في `league/commonmark` هو `html_input = allow` و`allow_unsafe_links = true` — يعني أي HTML خام (`<script>…`, `<img onerror=…>`) أو روابط `javascript:` جوّه المتن **بتتعرض زي ما هي** مش متهرّبة. التعليق في السطر 33 بيقول «html_input=escape (الافتراضي)» وده **غير صحيح** للمكتبة المستخدمة، فالاعتماد عليه خطر.
- **الأثر:** محرّر محتوى (دور editor، وهو أقل ثقة من admin) أو حساب أدمن متسرّب يقدر يزرع سكربت بيشتغل على زوّار الموقع وصفحات عامة → سرقة جلسات/تحويل. النطاق: صفحات `/blog/{post}` و`/pages/{slug}` المنشورة.
- **الحل المقترح:** مرّر options صريحة:
  ```php
  $html = Str::markdown($markdown, [
      'html_input' => 'strip',          // أو 'escape'
      'allow_unsafe_links' => false,
  ]);
  ```
  أو مرّر المخرجات على منقّي HTML (`stevenmaguire/…` أو `HTMLPurifier`). **الأولوية:** يتأكّد يدوياً بإدخال `<script>alert(1)</script>` في متن مقالة تجريبية ومعاينة الصفحة قبل الإطلاق.

> ملاحظة: `single.blade.php:31` و`shop/product.blade.php:215` بيستخدموا `nl2br(e($body))` → **متهرّب وآمن**. المشكلة محصورة في متن الـ Markdown المحوّل لـ HTML.

---

## مهم

### I1 — الـ webhook مابيتحقّقش إن المبلغ المدفوع = إجمالي الطلب
- **الملف:** [app/Http/Controllers/PaymentWebhookController.php:98](app/Http/Controllers/PaymentWebhookController.php:98) — `if ($result->isPaid() && ! $order->isPaid())`
- **المصدر:** [app/Payments/PaymobGateway.php:153-158](app/Payments/PaymobGateway.php:153) بيقرا `amount` من الحمولة لكن القيمة **مابتتقارنش** بـ `$order->total`.
- **المشكلة:** أي معاملة موقّعة صح بتعلّم الطلب «مدفوع» بغض النظر عن المبلغ. لو حصل عدم تطابق (خطأ في البوابة، أو تلاعب في جلسة الدفع) الطلب هيتأكّد بمبلغ ناقص.
- **الحل المقترح:** قبل `payment_status = 'paid'` قارن:
  ```php
  if (abs((float)$result->amount - (float)$order->total) > 0.01) {
      // سجّل تنبيه + سيب الطلب pending للمراجعة اليدوية، بلاش تعلّمه paid
  }
  ```

### I2 — webhook ناجح متأخّر بيعلّم طلب ملغى بالمهلة «مدفوع»
- **الملفات:** [app/Http/Controllers/PaymentWebhookController.php:98-106](app/Http/Controllers/PaymentWebhookController.php:98) + [app/Console/Commands/ExpirePendingPayments.php:30-39](app/Console/Commands/ExpirePendingPayments.php:30)
- **المشكلة:** الأمر المجدول بيلغي الطلب (`cancelled`) ويرجّع المخزون بعد المهلة. لو وصل webhook ناجح **بعد** الإلغاء، شرط `isPaid() && ! $order->isPaid()` بيتحقّق ويحوّل `payment_status` لـ `paid` — فيطلع طلب **«مدفوع» وحالته ملغاة ومخزونه رجع** (وربما اتباع لحد تاني).
- **الحل المقترح:** في `apply()` تجاهل/علّم للمراجعة لو `status` من ضمن `Order::STOCK_RETURNING` (cancelled/returned): بدل ما يتعلّم paid مباشرة، يتسجّل `payment_status = 'paid_after_cancel'` أو تنبيه للأدمن لإعادة الحجز يدوياً.

### I3 — اختطاف طلبات الضيف عبر التسجيل برقم موبايل الغير
- **الملف:** [app/Models/Customer.php:72-82](app/Models/Customer.php:72) — `linkGuestOrders()` + استدعاؤها في [CustomerAuthController.php:103-107](app/Http/Controllers/Auth/CustomerAuthController.php:103)
- **المشكلة:** عند التسجيل/الدخول بيتربط **أي طلب ضيف** رقم موبايله = موبايل الحساب، **من غير تحقق من ملكية الرقم (OTP)**. موبايل الطلب والتسجيل بيتطبّعوا لنفس الصيغة ([StoreOrderRequest.php:21-25](app/Http/Requests/StoreOrderRequest.php:21) و[RegisterCustomerRequest.php:20-26](app/Http/Requests/Auth/RegisterCustomerRequest.php:20))، فالمطابقة بتحصل فعلاً. يعني حد يسجّل برقم ضحية ويشوف طلباتها (الاسم/العنوان/المنتجات).
- **الحل المقترح:** تفعيل تحقق OTP للموبايل قبل ربط الطلبات، أو عدم الربط التلقائي والاكتفاء بالتتبّع (رقم + موبايل) اللي هو آمن أصلاً.

### I4 — المحرّر (editor) بيوصل لصفحة الطلبات وبيانات العملاء
- **الملف:** [app/Filament/Resources/OrderResource.php](app/Filament/Resources/OrderResource.php) — مش مستخدم فيه trait `AdminOnly` (على عكس Customer/User/MenuItem/Redirect).
- **المشكلة:** الدور editor بيقدر يفتح الطلبات وفيها بيانات شخصية (اسم/موبايل/عنوان/IP). الـ CLAUDE.md بيقول editor = «المحتوى فقط». ده **قرار صلاحيات**: لو المفروض مايشوفش الطلبات، لازم `AdminOnly`.
- **الحل المقترح:** لو الطلبات أدمن-أونلي: ضيف `use AdminOnly;` في `OrderResource` زي باقي الموارد الحساسة. (ملاحظة: `CustomerResource` متحمي صح فعلاً ✓).

### I5 — الثقة في كل البروكسيهات (`trustProxies('*')`)
- **الملف:** [bootstrap/app.php:17](bootstrap/app.php:17) — `$middleware->trustProxies(at: '*')`
- **المشكلة:** الثقة في أي مصدر لهيدرات `X-Forwarded-For/Host/Proto` تسمح بانتحال الـ IP (اللي بيعتمد عليه الـ throttle في login/checkout/tracking والتسجيل في الطلب) وانتحال الـ Host.
- **الحل المقترح:** في production حدّد IP/شبكة البروكسي المعروف (مثلاً Cloudflare ranges) بدل `'*'`.

---

## بسيط

### M1 — fallback رفع الصور بيخزّن بالامتداد الأصلي عند فشل التحويل
- **الملف:** [app/Support/ImageUploads.php:36-41](app/Support/ImageUploads.php:36)
- الـ FileUpload في Filament بيفرض `->image()` + `acceptedFileTypes` + `maxSize` قبل الاستدعاء (محمي ✓)، لكن لو فشل تحويل WebP بيتخزّن الأصل بامتداده. **دفاع في العمق:** اقصر الامتداد البديل على `jpg/png/webp` بس.

### M2 — `displayPrice()` بيرجّع سعر العرض حتى لو أكبر من السعر الأساسي
- **الملف:** [app/Models/Product.php:125-132](app/Models/Product.php:125) — `displayPrice()` بترجّع `sale_price` لو `> 0` من غير التأكد إنه أقل من `price` (على عكس `hasSale()`). لو الأدمن غلط وكتب `sale_price > price` هيتحسب في السلة والطلب. الحل: استخدم `min(sale_price, price)` أو اشترط `sale_price < price`.

### M3 — الـ custom head raw (مقبول لكن يُنبّه)
- **الملف:** [resources/views/layouts/partials/tracking-head.blade.php:42](resources/views/layouts/partials/tracking-head.blade.php:42) — `{!! $customHead !!}`. ده HTML خام من الإعدادات (admin-only عبر `ManagesSettings::canAccess` ✓). مقبول للتتبّع، بس لازم يفضل **أدمن-أونلي** ومايتفتحش للـ editor أبداً.

### M4 — فهارس (indexes) على أعمدة الاستعلام
- **المشكلة:** مفيش index على `orders.customer_phone` (يُستخدم في `linkGuestOrders` — [Customer.php:78](app/Models/Customer.php:78))، ومفيش index على `products.price/sale_price` (يُستخدموا في فلتر/ترتيب السعر — [ShopController.php:applyFilters/applySort](app/Http/Controllers/Content/ShopController.php)). التأثير محدود لأن الكتالوج صغير، لكن قبل النمو: ضيف index على `customer_phone` وفكّر في index على السعر.
- **إيجابي:** `payment_attempts.gateway_txn_id` فريد ✓، `orders.order_number` فريد ✓، `products.status/condition` و`is_published` مفهرسين ✓.

### M5 — N+1 خفيف في السايت ماب
- **الملف:** [app/Http/Controllers/SitemapController.php:211-221](app/Http/Controllers/SitemapController.php:211) و`blogCategoryUrls` — `exists()`/`max()` لكل قسم/تصنيف. محدود بعدد الأقسام، والناتج **متكاش 600 ثانية** ✓. مقبول.

### M6 — تطبيع الموبايل غير موحّد في مكان واحد
- `StoreOrderRequest` و`OrderController::track` بيشيلوا المسافات بس، و`Customer::normalizePhone` بتشيل كل غير-الأرقام (بتحافظ على `+`). للأرقام المصرية `01xxxxxxxxx` النتيجة واحدة، فمفيش كسر وظيفي حالياً — بس يُفضّل توحيد الدالة لمصدر واحد لتفادي اختلاف مستقبلي.

---

## الدفع والطلبات — تحقّقات نجحت ✓
- الأسعار/الإجمالي/رسوم التوصيل **server-side بالكامل** من الداتابيز، ومفيش ثقة في أي قيمة من المتصفح — [OrderCreator.php:52-90](app/Support/Store/OrderCreator.php:52)، [Cart.php](app/Support/Store/Cart.php) (السلة بتخزّن `[id=>qty]` بس).
- منع البيع المزدوج: `DB::transaction` + `lockForUpdate` + إعادة فحص المخزون تحت القفل، والمستعمل كمية 1 — [OrderCreator.php:40-66](app/Support/Store/OrderCreator.php:40). مغطّى باختبار «last piece cannot be ordered twice» و«rechecks stock under lock».
- الـ webhook: تحقق HMAC-SHA512 بـ `hash_equals` ([PaymobGateway.php:114-126](app/Payments/PaymobGateway.php:114))، رفض غير الموقّع (403) مع تسجيل للتدقيق، idempotency بقفل الصف + **قيد فريد `gateway_txn_id`** على مستوى الداتابيز ([migration payment_attempts](database/migrations/2026_10_19_090100_create_payment_attempts_table.php))، وصفحة الرجوع مجرد تحويل مش دليل ([PaymentWebhookController.php:60-65](app/Http/Controllers/PaymentWebhookController.php:60)).
- المهلة وإرجاع المخزون عند الفشل/الإلغاء شغّالين ومجدولين كل 5 دقايق — [routes/console.php](routes/console.php) + [ExpirePendingPayments.php](app/Console/Commands/ExpirePendingPayments.php). مغطّى بـ `ExpirePendingPaymentsTest`.
- **مفيش تخزين بيانات كروت** — التحويل لـ hosted checkout بتاع Paymob، والمفاتيح من `config/services` (من `.env`) مش في الكود ولا الداشبورد — [PaymobGateway.php:194-217](app/Payments/PaymobGateway.php:194). الحمولة المخزّنة في `payment_attempts.payload` فيها آخر 4 أرقام والشبكة بس.

## الصلاحيات / IDOR — تحقّقات نجحت ✓
- `/account/orders/{order}` بيتأكّد إن `order->customer_id === customer->id` وإلا 404 — [Account/OrdersController.php:44-46](app/Http/Controllers/Account/OrdersController.php:44).
- التتبّع بيطلب **رقم + موبايل** سوا، مفيش كشف ببارامتر واحد — [OrderController.php:52-77](app/Http/Controllers/OrderController.php:52).
- صفحة التأكيد session-gated (`last_order_id`) مش برقم الطلب — [OrderController.php:21-35](app/Http/Controllers/OrderController.php:21).
- `retry` الدفع محمي بـ session gate + حالة الطلب + المهلة — [CheckoutController.php:113-139](app/Http/Controllers/CheckoutController.php:113).
- gating الأدمن: `AdminOnly` على Customer/User/MenuItem/Redirect، والإعدادات أدمن-أونلي — مغطّى باختبار «editor is blocked from admin only resources». (استثناء الطلبات: شوف I4).

## الحماية العامة — تحقّقات نجحت ✓
- CSRF مفعّل على كل الفورمات (Filament + web)، والاستثناء الوحيد هو webhook الدفع (محمي بـ HMAC) — [bootstrap/app.php:24-26](bootstrap/app.php:24).
- throttle على: booking (6/د)، checkout (10/د)، tracking (15/د)، login (10/د)، register (6/د)، cart/wishlist/compare (30/د)، review/retry (10/د) — [routes/web.php](routes/web.php).
- Validation من السيرفر عبر FormRequests + honeypot (`website` prohibited) — [StoreOrderRequest](app/Http/Requests/StoreOrderRequest.php)، [RegisterCustomerRequest](app/Http/Requests/Auth/RegisterCustomerRequest.php).
- `APP_DEBUG` افتراضي **false** — [config/app.php:42](config/app.php:42).
- رفع الملفات: `->image()` + نوع + حجم على كل الحقول (شوف M1 للملاحظة).
- XSS: معظم المخرجات متهرّبة بـ `{{ }}`/`e()`؛ الاستثناء الخطر في C1.
- مسار الداشبورد `/admin` قياسي مع auth + CSRF — [AdminPanelProvider.php](app/Providers/Filament/AdminPanelProvider.php).

## SEO المتجر — تحقّقات نجحت ✓
- السلة/checkout/التأكيد/الحساب/التتبّع/login/register كلها **noindex,nofollow** — مغطّى باختبار «cart and checkout are noindex».
- فلاتر/بحث/ترتيب/صفحات → `noindex,follow` والكانونيكال يرجع لصفحة القسم الأساسية — [ShopController.php:category](app/Http/Controllers/Content/ShopController.php) + اختبار «filtered category is noindex».
- Product schema فيه سعر حقيقي وتوافر حقيقي، **offers بتظهر بس لو فيه سعر > 0**، و`AggregateRating`/`Review` **من التقييمات المعتمدة بالداتابيز فقط** (مفيش تقييمات مفبركة) — [schema/product.blade.php:29-68](resources/views/components/schema/product.blade.php:29) + اختبار «product rating schema only after approval».
- المنتج المباع: 200 + `noindex` بعد 60 يوم ويخرج من السايت ماب؛ غير المنشور 404 — [Product.php:234-246](app/Models/Product.php:234)، [SitemapController.php:163-172](app/Http/Controllers/SitemapController.php:163) + اختبارات «sold used product is 200 and noindex after 60 days» و«products sitemap excludes stale sold».
- مفيش صفحات placeholder منشورة في السايت ماب (الفاضي يفضل noindex ومابيدخلش) — [SitemapController.php:107-121](app/Http/Controllers/SitemapController.php:107).

## الأداء — ملاحظات
- Eager loading مطبّق في الاستعلامات الحساسة (`with(['primaryImage','category','brand',...])`) → مفيش N+1 واضح في صفحات القائمة/المنتج.
- caching: السايت ماب متكاش 10 دقائق، والإعدادات/الكتالوج متكاشة وبتتمسح تلقائياً عند الحفظ (observers).
- الصور: تحويل WebP + تصغير لعرض أقصى 1600px جودة 82 — [ImageUploads.php](app/Support/ImageUploads.php).
- شوف M4 (فهارس) وM5 (N+1 خفيف متكاش) للتحسينات الاختيارية.

## الاختبارات
- **النتيجة:** `php artisan test` → **51 passed / 189 assertions** (14.3s).
- **تغطية ممتازة:** المخزون تحت القفل، آخر قطعة مرة واحدة، الـ webhook (موقّع/غير موقّع/مكرّر/فاشل/نوع مختلف)، المهلة وإرجاع المخزون، التتبّع، noindex، صلاحيات الأدمن، التقييمات بعد الاعتماد.
- **فجوات تغطية مقترحة (مسارات حرجة من غير اختبار):**
  1. **تطابق المبلغ في الـ webhook** (I1) — مفيش اختبار لأن مفيش فحص أصلاً.
  2. **webhook ناجح بعد إلغاء المهلة** (I2) — غير مغطّى.
  3. **IDOR على `/account/orders/{id}`** بين عميلين — الفحص موجود بس من غير اختبار يثبته.
  4. **اختطاف طلبات الضيف بالموبايل** (I3) — غير مغطّى.
  5. **session gate لإعادة محاولة الدفع** (`retry`) — غير مغطّى.

---

### الأولويات المقترحة قبل الإطلاق
1. **حسم C1** (XSS الماركداون) — تحقّق يدوي + تمرير options، تريفيال وعالي الأثر.
2. **I1 + I2** (سلامة الدفع: تطابق المبلغ + معالجة الدفع المتأخّر على طلب ملغى).
3. **I3 + I4** (قرارات خصوصية/صلاحيات: ربط الطلبات بالموبايل، ووصول المحرّر للطلبات).
4. **I5** ثم البسيط (M1–M6) حسب الوقت.

**مستني موافقتك على الأولويات قبل ما أصلّح أي حاجة.**

---

## حالة الإصلاحات — فرع `fix/prelaunch-review` (2026-10-19)

كل بند اتعمله اختبار آلي يثبته. النتيجة النهائية: **`php artisan test` → 77 passed (254 assertions)** (كانت 51).

| البند | الحالة | الملخّص |
|------|--------|---------|
| **C1** — XSS الماركداون + JSON-LD | ✅ اتصلّح | `Str::markdown` بقت بـ `html_input=strip` + `allow_unsafe_links=false` والتعليق اتصحّح. كل الـ JSON-LD (11 مكوّن بيعدّوا على `graph.blade.php`) زادوا `JSON_HEX_TAG\|AMP\|APOS\|QUOT`. |
| **I1** — تطابق المبلغ/العملة | ✅ اتصلّح | الـ webhook بيقارن المبلغ (قروش→جنيه) بإجمالي الطلب والعملة لازم `EGP`. عدم التطابق → `payment_status=amount_mismatch` + Log + badge/فلتر في Filament (مش `paid`). |
| **I2** — دفع متأخّر على طلب ملغى | ✅ اتصلّح | لو الطلب `cancelled/returned` → `payment_status=paid_after_cancel` (مش `paid`، الحالة متترجعش). Filament action «إعادة تفعيل الطلب» بيفحص المخزون تحت lock وينجح بس لو متاح، وإلا بيطلب استرداد. |
| **I3** — اختطاف طلبات الضيف | ✅ اتصلّح | اتشال `Customer::linkGuestOrders()` واستدعاؤها. التتبّع (رقم+موبايل) وحساب العميل (طلبات المسجّل) زي ما هما. |
| **I4** — وصول المحرّر للطلبات | ✅ اتصلّح | `use AdminOnly` على `OrderResource`. المحرّر بياخد 403 على `/admin/orders` (مفيش widgets/global-search بتكشف بيانات الطلبات). |
| **I5** — trustProxies('*') | ✅ اتصلّح | بقى من `TRUSTED_PROXIES` (IPs/CIDR، الافتراضي: لا شيء)، و`*` ممنوع في production. موثّق في `CLAUDE.md`. **⚠️ `.env.example` محمي بقاعدة deny — محتاج تضيف السطر يدوياً (تحت).** |
| **M1** — fallback امتداد الصورة | ✅ اتصلّح | الامتداد البديل مقصور على `jpg/jpeg/png/webp` وإلا `jpg`. |
| **M2** — displayPrice | ✅ اتصلّح | بيستخدم `hasSale()` (سعر العرض بس لو أقل من الأساسي). |
| **M4** — فهارس | ✅ اتصلّح | migration جديدة: index على `orders.customer_phone` و`products.price` و`products.sale_price`. |
| **M6** — توحيد تطبيع الموبايل | ✅ اتصلّح | `App\Support\PhoneNumber::normalize()` مصدر واحد (الطلب/التتبّع/التسجيل). بيوحّد `01…` و`+201…` و`201…` لصيغة `01…`. |
| **M3** — custom head raw | ⏸️ مؤجل (مقبول) | أدمن-أونلي أصلاً عبر `ManagesSettings::canAccess` — مفيش تغيير. |
| **M5** — N+1 خفيف في السايت ماب | ⏸️ مؤجل (مقبول) | متكاش 600 ثانية ومحدود بعدد الأقسام. |

### الاختبارات المضافة
- `tests/Feature/MarkdownSecurityTest.php` (C1): شيل `<script>`/`<img onerror>`/`javascript:`، و`</script>` في قيمة schema مبتكسرش الصفحة.
- `tests/Feature/PaymentWebhookTest.php` (I1/I2): مبلغ ناقص/زيادة/عملة مختلفة → `amount_mismatch`؛ مطابق → `paid`؛ دفع متأخّر على طلب ملغى → `paid_after_cancel`؛ إعادة تفعيل مع مخزون متاح/مباع.
- `tests/Feature/AccountTest.php` (I3): عميل جديد بموبايل عليه طلبات ضيف مبيشوفهاش (اتصحّح الاختبار القديم).
- `tests/Feature/AdminAccessTest.php` (I4): المحرّر 403 على الطلبات؛ الأدمن 200.
- `tests/Unit/PhoneNumberTest.php` (M6) + `tests/Feature/ProductPricingTest.php` (M2) + `tests/Feature/ImageUploadsTest.php` (M1).
- `tests/Feature/CheckoutRetryTest.php` (فجوة): session gate على retry الدفع. (IDOR على `/account/orders/{id}` مغطّى بـ `AccountTest::test_customer_cannot_view_another_customers_order`.)

### الملفات المعدّلة
`app/Support/ArticleContent.php`، `resources/views/components/schema/graph.blade.php`، `app/Payments/GatewayResult.php`، `app/Payments/PaymobGateway.php`، `app/Http/Controllers/PaymentWebhookController.php`، `app/Models/Order.php`، `app/Filament/Resources/OrderResource.php`، `app/Models/Customer.php`، `app/Http/Controllers/Auth/CustomerAuthController.php`، `bootstrap/app.php`، `app/Models/Product.php`، `app/Support/ImageUploads.php`، `app/Support/PhoneNumber.php` (جديد)، `app/Http/Requests/StoreOrderRequest.php`، `app/Http/Controllers/OrderController.php`، `CLAUDE.md`، migration جديدة `2026_10_20_000000_add_prelaunch_indexes.php`.

### webapp-testing (دورة شراء كاش كاملة + تتبّع)
دورة كاملة على السيرفر المحلي: إضافة منتج للسلة → checkout بموبايل بصيغة `+20 101 234 5670` → الطلب اتعمل (`KH-261019-8511`, إجمالي 5,060) والتأكيد عرض الرقم مطبّع `01012345670` → التتبّع بصيغة `201012345670` طابق الطلب وعرضه. **مفيش أخطاء Console**، والموبايل والكمبيوتر تمام.

### ⚠️ مطلوب منك يدوياً (`.env.example`)
الملف محمي بقاعدة deny فمقدرتش أعدّله. ضيف السطر ده:
```
# بروكسيهات موثوقة لقراءة X-Forwarded-* (IPs/CIDR مفصولة بفاصلة). الافتراضي: فاضي.
# ممنوع '*' في production. ورا Cloudflare: حط نطاقاتهم. مباشر: سيبها فاضية.
TRUSTED_PROXIES=
```

---

## مرحلة K2 — سير المراجعة + صفحات SEO من الداشبورد (فرع `feature/seo-keywords`)

**القاعدة الجديدة (مفروضة بالكود):** مفيش صفحة تتنشر من غير مراجعة الفني + صفر TODO. النشر مشروط بـ
`content_status='ready'` + `reviewed_by` + صفر TODO في المحتوى + الحقول المطلوبة (`canPublish()`). المسوّدة = 404
للزائر + معاينة للأدمن (شريط «مسودة» + `X-Robots-Tag: noindex`). بيتطبّق على 5 أنواع صفحات. تحذير تشابه > 30% عند النشر.

**اختبارات:** `php artisan test` → **96 نجحت** (منها 11 جديدة في `ReviewWorkflowTest`).

### زحف داخلي محلي
اتزحف **60 رابط منشور** (الرئيسية + الفهارس + الصفحات التعريفية + الخدمات/الماركات/المناطق/المقالات + sitemap/robots) — **كلها 200، صفر روابط مكسورة أو يتيمة**.

### قياس التشابه بين صفحات الماركات (Section 5)
11 ماركة منشورة، 55 زوج. **المتوسط 37%**، و**الأعلى 77%** (`fresh × union-air`, و`gree × toshiba`). ⚠️ **تنبيه SEO:**
نصوص الماركات متشابهة أوي (بتتشارك boilerplate «بنصلّح كل الماركات / مش توكيل...»). يُفضّل صاحب الموقع/الفني
يميّز مقدمة وأعطال كل ماركة أكتر قبل الاعتماد عليها للـ SEO العضوي. (حد التحذير عند النشر = 30%.)

### الرقم `01000000000` (placeholder — يتغيّر من الداشبورد «إعدادات الموقع»)
موجود في: `config/site.php` (القيمة الافتراضية/fallback)، `app/Support/Site.php` (الثابت اللي بيحدد إن الرقم placeholder
فيتشال من الـ schema)، `app/Filament/Pages/ManageSiteSettings.php` (placeholder في الحقل)، و`tests/Feature/AdminAccessTest.php`
(بيانات اختبار). **مفيش نص ثابت للرقم في الـ Blade** — كله بيقرأ من `site('phone')`. غيّره من الداشبورد مش من الكود.

### favicon و og-image (مطلوب رفعهم يدوياً)
- **favicon:** الموقع بيطلب `public/favicon.ico` (اللينك اتضاف في الـ layout). ارفع الملف هناك.
- **og-image:** المسار المتوقع `public/images/og-image.png` (من `config/site.default_og_image`). ارفع الصورة هناك.

---

## مرحلة K2 — المناطق + صفحات (منطقة×خدمة) + بوابة إطلاق (فرع `feature/areas-articles`)

مبني على تقرير الكلمات (`docs/keywords/KEYWORD_MAP.md`): أعلى نية بحث للمناطق هي خدمة («صيانة تكييف {المنطقة}»، «فني تكييف {المنطقة}»).

- **هيكل صفحة المنطقة:** الأحياء والكمبوندات بقت repeater `{name, type}`، H1 «صيانة تكييف {المنطقة}» + استهداف «فني تكييف {المنطقة}» طبيعي، وباقي الأقسام (المشاكل الشائعة، زمن الوصول، أسئلة العملاء، الخدمات الأكثر طلباً) تتخفى لو فاضية. المحتوى المحلي من صاحب الموقع.
- **صفحات (منطقة×خدمة):** نوع صفحة جديد `/areas/{area}/{service}` بنفس سير المراجعة + معاينة الأدمن + السايت ماب `sitemap-area-services.xml`. action «إنشاء صفحات (منطقة×خدمة)» **بحد أقصى 5 تركيبات في المرة** (مفيش توليد بالجملة)، القالب يتعدّل من الداشبورد.
- **فيصل:** اتضافت **مسودة** (محتوى ناقص + TODO) محتاجة تأكيد إننا بنخدمها قبل النشر. المناطق السبعة الأولوية فضلت منشورة زي ما هي.
- **`site:preflight`:** أمر بوابة إطلاق — بيفشل في production لو: `APP_DEBUG=true`، `canonical_host` فاضي/placeholder، الهاتف/الواتساب فاضي/placeholder، `favicon`/`og-image` ناقصين، `TRUSTED_PROXIES='*'`، أو أي صفحة منشورة فيها TODO. بره الإنتاج تحذيرات بس.
- **رقم الهاتف الافتراضي بقى فاضي:** `phone`/`phone_tel`/`whatsapp = ''` في config. **أزرار الاتصال/الواتساب بتتخفى** لو الرقم فاضي أو placeholder (عبر `Site::hasPhone()`/`hasWhatsapp()`) — في الهيدر والفوتر والأزرار العائمة وصفوف CTA. صاحب الموقع يحط الأرقام من الداشبورد.

**اختبارات:** `php artisan test` → **104 نجحت** (منها 8 جديدة في `AreasK2Test`).

**تشابه صفحات المناطق:** 17 منطقة، 136 زوج — **المتوسط 8.3%، الأعلى 28%** (`dokki × mohandessin`) — **تحت حد 30%** (محتوى المناطق فريد فعلاً، أحسن بكتير من تشابه الماركات).
