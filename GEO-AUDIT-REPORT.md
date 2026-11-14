# GEO Audit Report: خدماتي (Khadamaty)

**Audit Date:** 2026-11-14
**URL:** https://darkcyan-jackal-249159.hostingersite.com/
**Business Type:** Local Business — HVAC / خدمات صيانة تكييفات (Services + متجر قطع غيار)
**Pages Analyzed:** الرئيسية + صفحات الخدمات/المناطق/الماركات/المدونة/المتجر (عيّنة) + robots.txt + llms.txt
**Method:** تحليل مباشر من كود الموقع + فحص لايف (schema، meta، robots، SSR)

---

## Executive Summary

**Overall GEO Score: 54/100 (Poor — أساس تقني قوي بس سلطة علامة شبه معدومة)**

موقع خدماتي **مبني كويس جداً من الناحية التقنية والـ schema** — ده من أقوى نقاطه: structured data غني (Organization + HVACBusiness + WebSite + FAQPage + Service + Product + Article + Breadcrumb)، render من السيرفر (SSR) فالـ AI بيقرا المحتوى كامل، canonical و hreflang و OG مظبوطين، ومحتوى عربي منظّم بأسئلة شائعة وأعراض وخطوات (قابل للاقتباس من الـ AI). 

لكن بيضعّفه **عاملين كبار**: (١) **robots.txt الحالي بيحظر Googlebot من الموقع كله** (ده إعداد الدومين المؤقت من Hostinger، مش كود الموقع)، و(٢) **صفر سلطة خارجية** — دومين جديد، مفيش llms.txt، مفيش منشن على مواقع يعتمد عليها الـ AI (جوجل بزنس/ويكيبيديا/ريديت/يوتيوب)، والأرقام والتقييمات لسه placeholders. النتيجة: الأساس ممتاز لكن الـ AI لسه مش «عارف» البراند.

### Score Breakdown

| Category | Score | Weight | Weighted |
|---|---|---|---|
| AI Citability | 68/100 | 25% | 17.0 |
| Brand Authority | 22/100 | 20% | 4.4 |
| Content E-E-A-T | 52/100 | 20% | 10.4 |
| Technical GEO | 66/100 | 15% | 9.9 |
| Schema & Structured Data | 85/100 | 10% | 8.5 |
| Platform Optimization | 35/100 | 10% | 3.5 |
| **Overall GEO Score** | | | **54/100** |

---

## Critical Issues (Fix Immediately)

### 1. 🔴 robots.txt اللايف بيحظر Googlebot من الموقع بالكامل
الـ robots.txt الظاهر على الدومين دلوقتي:
```
User-agent: Googlebot
Disallow: /

User-agent: *
Allow: /
```
ده **مش كود الموقع** (RobotsController بتاعك بيسمح للجميع) — ده ملف ثابت بيحطه Hostinger على الدومين المؤقت (`*.hostingersite.com`) عشان الموقع التجريبي مايتفهرسش. **النتيجة:** جوجل (ومعاه Google AI Overviews) **ممنوع تماماً**. الزواحف التانية بما فيهم زواحف الـ AI (`*`) مسموحة.
**الحل:** اربط **دومين حقيقي** (مثلاً khadamaty.com) — ساعتها robots.txt بتاع لارافيل هو اللي يشتغل ويسمح لجوجل. ولحد ما تربط الدومين، الموقع عملياً مخفي عن بحث جوجل.

---

## High Priority Issues (Fix Within 1 Week)

### 2. 🟠 مفيش ملف llms.txt
مفيش `/llms.txt` (المعيار الصاعد اللي بيساعد أنظمة الـ AI تفهم بنية الموقع وأهم صفحاته). غيابه بيخلّي الـ AI يعتمد على التخمين من الزحف.
**الحل:** ولّد `llms.txt` فيه وصف البراند + روابط أهم الصفحات (الخدمات/المناطق/المدونة). مهارة `/geo-llmstxt` تقدر تولّده.

### 3. 🟠 صفر سلطة علامة خارجية (Brand Authority)
الـ AI بيقرّر يقتبس مين بناءً على ذكر البراند في مصادر موثوقة. حالياً: مفيش Google Business Profile، مفيش ويكيبيديا، مفيش منشن على ريديت/يوتيوب، والـ `sameAs` (روابط السوشيال في الـ schema) غالباً فاضية.
**الحل:** ابدأ بـ **Google Business Profile** (أهم حاجة لنشاط محلي)، واملأ روابط السوشيال من الداشبورد عشان تتحط في schema الـ Organization.

### 4. 🟠 بيانات التواصل والتقييمات لسه placeholders
الهاتف/الواتساب/التقييمات لسه مش متملّيين (حسب قواعد المشروع — ممنوع أرقام/تقييمات وهمية). ده بيضعّف إشارات الثقة اللي الـ AI بيدوّر عليها (contactPoint، aggregateRating).
**الحل:** من الداشبورد: املأ الهاتف + الواتساب + ساعات العمل + نطاق الضمان. التقييمات اجمعها حقيقية من العملاء.

---

## Medium Priority Issues (Fix Within 1 Month)

### 5. 🟡 E-E-A-T: مفيش مؤلّف/مصداقية على المقالات
مقالات المدونة مالهاش author bio بأسماء/خبرة واضحة، ولا استشهادات بمصادر — ده بيقلّل إشارات الخبرة/الموثوقية.
**الحل:** أضف صفحة/بايو للفني أو الكاتب بخبرته (سنين الخبرة، تخصص)، واربطها بـ schema `Person` كـ author.

### 6. 🟡 تغطية الأسئلة الشائعة (FAQ) مش على كل الصفحات
الـ FAQPage schema موجود على الرئيسية وبعض الصفحات، لكن كل صفحة خدمة/منطقة المفروض يكون فيها أسئلة شائعة (الـ AI بيحب الـ Q&A القابل للاقتباس).
**الحل:** أضف ٣–٥ أسئلة شائعة حقيقية لكل صفحة خدمة ومنطقة من الداشبورد.

### 7. 🟡 إشارات «إجابة مباشرة» في أول المحتوى
الـ AI بيقتبس الفقرات اللي بتجاوب سؤال بشكل مباشر ومختصر في الأول.
**الحل:** ابدأ كل صفحة خدمة بجملة تعريفية مباشرة (مثلاً: «صيانة التكييف هي… وبتشمل… وبتاخد…») قبل التفاصيل.

---

## Low Priority Issues (Optimize When Possible)

- أضف `speakable` schema للأجزاء القابلة للقراءة الصوتية (مفيد للمساعدات الصوتية).
- `aggregateRating` مش موجود (طبيعي — مفيش تقييمات لسه)؛ يتضاف أول ما تتجمع تقييمات حقيقية.
- تأكد إن كل الصور عندها alt (معظمها مظبوط عبر مكتبة الميديا).
- فكّر في إضافة بيانات `OpeningHoursSpecification` و`GeoCoordinates` لمناطق الخدمة.

---

## Category Deep Dives

### AI Citability (68/100) — جيد
- ✅ محتوى عربي منظّم: أعراض، «الخدمة بتشمل»، خطوات العمل، أسئلة شائعة — كله قوالب قابلة للاستخراج.
- ✅ FAQPage schema بيساعد الاقتباس المباشر.
- ⚠️ ينقص «جُمل تعريفية مباشرة» في أول كل صفحة، وبعض الصفحات محتواها متوسط العمق.

### Brand Authority (22/100) — حرج
- ❌ دومين جديد، مفيش حضور على المنصّات اللي الـ AI بيتدرّب/بيستشهد بيها.
- ❌ `sameAs` (سوشيال) غالباً فاضي، مفيش Google Business Profile.
- 👉 ده أكبر فرصة تحسين — نشاط محلي بيستفيد جداً من GBP + منشن محلي.

### Content E-E-A-T (52/100) — ضعيف/متوسط
- ✅ صفحة «مين إحنا» موجودة، ومحتوى الخدمات تفصيلي وعملي (Experience).
- ⚠️ مفيش author/credentials على المقالات، التقييمات placeholders، إشارات الثقة (ضمان) شرطية.

### Technical GEO (66/100) — جيد (مع خصم كبير للـ robots)
- ✅ SSR كامل (Blade) — الـ AI بيقرا المحتوى من غير JS.
- ✅ canonical، robots meta (index,follow)، hreflang (ar-eg + x-default)، OG/Twitter، sitemap، RTL/lang، صور WebP.
- ✅ زواحف الـ AI (`*`) مسموحة في robots.
- 🔴 بس Googlebot محظور على الدومين المؤقت (شوف Critical #1)، ومفيش llms.txt.

### Schema & Structured Data (85/100) — ممتاز
- ✅ على الرئيسية: `Organization` + `HVACBusiness` (نوع LocalBusiness مثالي للنشاط) + `WebSite` + `FAQPage`.
- ✅ متاح لكل نوع صفحة: `Service`، `Product`، `Article`، `BreadcrumbList`، `ItemList`، `Review`.
- ⚠️ ينقص: `sameAs` مملوء، `aggregateRating`، `speakable`.

### Platform Optimization (35/100) — ضعيف
- ✅ وصول زواحف الـ AI متاح (ChatGPT/Perplexity/Claude ممكن يزحفوا).
- ❌ مفيش حضور على Reddit/YouTube/Wikipedia/GBP اللي بيغذّي ذاكرة الـ AI عن البراند.

---

## Quick Wins (Implement This Week)

1. **اربط دومين حقيقي** → بيفك حظر Googlebot تلقائياً ويفعّل robots.txt الصحيح بتاع الموقع. (أكبر أثر مفرد.)
2. **أنشئ Google Business Profile** للنشاط (الاسم/العنوان/الهاتف/ساعات العمل) — أهم إشارة لنشاط محلي.
3. **املأ الهاتف + الواتساب + السوشيال + ساعات العمل** من الداشبورد → بيفعّل أزرار التواصل + بيملأ `sameAs`/`contactPoint` في الـ schema.
4. **ولّد `llms.txt`** (عبر `/geo-llmstxt`) وارفعه.
5. **أضف جملة تعريفية مباشرة** في أول كل صفحة خدمة (إجابة مختصرة يقتبسها الـ AI).

## 30-Day Action Plan

### Week 1: الأساس والوصول
- [ ] ربط دومين حقيقي (يفك حظر Googlebot).
- [ ] إنشاء Google Business Profile.
- [ ] ملء الهاتف/الواتساب/ساعات العمل/السوشيال من الداشبورد.

### Week 2: قابلية الاقتباس (Citability)
- [ ] إضافة جملة تعريفية مباشرة أول كل صفحة خدمة.
- [ ] إضافة ٣–٥ أسئلة شائعة حقيقية لكل صفحة خدمة ومنطقة.
- [ ] توليد ورفع `llms.txt`.

### Week 3: E-E-A-T والثقة
- [ ] بايو للفني/الكاتب بخبرته + ربطه كـ author (schema Person).
- [ ] بدء تجميع تقييمات حقيقية من العملاء (تظهر في aggregateRating).

### Week 4: سلطة العلامة
- [ ] إنشاء/تحديث صفحات سوشيال (فيسبوك/إنستجرام) واستخدامها.
- [ ] نشر محتوى مفيد (أدلة صيانة) يجذب منشن/روابط محلية.
- [ ] إعادة تشغيل `/geo-audit` لقياس التقدّم (قارن بـ `/geo-compare`).

---

## Appendix: ملخص الإشارات المرصودة

| الإشارة | الحالة |
|---|---|
| robots.txt (لايف) | 🔴 يحظر Googlebot (دومين Hostinger المؤقت) — `*` مسموح |
| robots.txt (كود الموقع) | ✅ يسمح للجميع، يمنع admin/cart/checkout فقط |
| llms.txt | ❌ غير موجود |
| Schema (الرئيسية) | ✅ Organization, HVACBusiness, WebSite, FAQPage |
| Schema (متاح) | ✅ Service, Product, Article, Breadcrumb, ItemList, Review |
| SSR / قابلية القراءة | ✅ Blade server-rendered |
| canonical / robots meta | ✅ موجود، index,follow |
| hreflang | ✅ ar-eg + x-default |
| OG / Twitter | ✅ موجود |
| meta description | ✅ عربي وصفي |
| sameAs (سوشيال) | ⚠️ غالباً فاضي |
| aggregateRating | ❌ (مفيش تقييمات لسه) |
| Google Business Profile | ❌ غير مُعدّ |
| الهاتف/الواتساب | ⚠️ placeholders لحد ما تتملّى |

---

*تقرير مبدئي مبني على تحليل الكود + فحص لايف. أهم خطوة فورية: ربط دومين حقيقي لفك حظر Googlebot. بعد تطبيق خطة الـ 30 يوم، المتوقع قفزة كبيرة في النتيجة (خصوصاً Brand Authority و Technical).*
