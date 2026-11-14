# AI Crawler Access Report: khadamaty-ac.com

**Analysis Date:** 2026-11-14
**Domain:** https://khadamaty-ac.com
**robots.txt Status:** Found (served by the Laravel app — RobotsController)

---

## 🟢 AI Visibility Score: 100/100 — ممتاز

كل زواحف الـ AI مسموح لها تزحف المحتوى. مفيش أي حظر لزاحف AI، مفيش حظر شامل (`Disallow: /`)، و`llms.txt` و`sitemap.xml` موجودين. ده أفضل وضع ممكن للوصول.

**Tier 1 Access:** 5/5 crawlers allowed
**Tier 2 Access:** 5/5 crawlers allowed
**Tier 3 Access:** 4/4 (مسموحين — غير محظورين)

---

## Crawler Access Summary

| Crawler | Operator | Tier | Status | ملاحظة |
|---|---|---|---|---|
| GPTBot | OpenAI | 1 | ✅ Allowed | ChatGPT Search — أعلى أثر |
| OAI-SearchBot | OpenAI | 1 | ✅ Allowed | بحث ChatGPT (بدون تدريب) |
| ChatGPT-User | OpenAI | 1 | ✅ Allowed | زيارة المستخدم المباشرة |
| ClaudeBot | Anthropic | 1 | ✅ Allowed | بحث وتحليل Claude |
| PerplexityBot | Perplexity | 1 | ✅ Allowed | أفضل مصدر referral بيستشهد بالمصادر |
| Google-Extended | Google | 2 | ✅ Allowed | Gemini / AI Overviews |
| GoogleOther | Google | 2 | ✅ Allowed | أبحاث Google AI |
| Applebot-Extended | Apple | 2 | ✅ Allowed | Apple Intelligence |
| Amazonbot | Amazon | 2 | ✅ Allowed | Alexa |
| FacebookBot | Meta | 2 | ✅ Allowed | Meta AI |
| CCBot | Common Crawl | 3 | ✅ Allowed | بيانات تدريب |
| anthropic-ai | Anthropic | 3 | ✅ Allowed | تدريب Claude |
| Bytespider | ByteDance | 3 | ✅ Allowed | TikTok/Doubao (ممكن تحظره — اختياري) |
| cohere-ai | Cohere | 3 | ✅ Allowed | تدريب Cohere |

> **ملاحظة مهمة:** الزواحف دي مش مذكورة بالاسم في robots.txt، لكنها بتورث قاعدة `User-agent: *` اللي بتسمح بكل المحتوى (وبتمنع بس صفحات الإدارة والسلة والدفع). يعني **كلها مسموحة فعلياً**.

---

## robots.txt الحالي (على الدومين الجديد)

```
User-agent: *
Disallow: /admin
Disallow: /vendor-dashboard
Disallow: /cart
Disallow: /checkout
Disallow: /order/
Disallow: /track
Disallow: /account
Disallow: /wishlist
Disallow: /compare
Disallow: /login
Disallow: /register

Sitemap: https://khadamaty-ac.com/sitemap.xml
```

✅ ده إعداد سليم: بيمنع الصفحات الخاصة بس (إدارة/سلة/حساب)، وبيسيب كل المحتوى العام مفتوح للزواحف.

---

## Additional Technical Findings

- **Meta Robots Tags:** الصفحات العامة `index,follow` ✅ (الصفحات غير المنشورة بس عليها noindex).
- **X-Robots-Tag Headers:** مفيش noindex على الصفحات العامة ✅.
- **JavaScript Rendering:** الموقع **SSR كامل** (Blade) — الزواحف محدودة الـ JS (GPTBot/ClaudeBot/PerplexityBot) بتقرا المحتوى كامل ✅.
- **llms.txt:** ✅ موجود وديناميكي (`/llms.txt`).
- **sitemap.xml:** ✅ موجود ومُعلن في robots.txt.
- **Cloudflare Managed robots.txt:** غير موجود (السيرفر LiteSpeed مباشر على Hostinger — مفيش حظر مخفي من Cloudflare) ✅.
- **ai.txt / .well-known/ai-plugin.json:** غير موجودين (معايير مقترحة/نيتش — مش مؤثرة).

---

## Content Signals (IETF Draft)

**Status:** Absent — الموقع مصرّحش بتفضيلاته تجاه الـ AI بشكل صريح.

**توصية (اختيارية — تحسين مش إصلاح):** تقدر تضيف سطر `Content-Signal` في robots.txt يعلن إنك موافق على استخدام الـ AI:

```
Content-Signal: ai-train=yes, search=yes, ai-retrieval=yes
```

ده بيوضّح نيتك صراحةً لأنظمة الـ AI. (الوصول بالفعل مفتوح من غيره.)

---

## Recommendations

### ✅ لا يوجد أي مشكلة حرجة
كل زواحف الـ AI المهمة مسموحة. الوضع الحالي ممتاز.

### تحسينات اختيارية (nice-to-have)
1. **إضافة Allow صريحة لزواحف الـ AI** في robots.txt — للوضوح (رغم إن الوصول مفتوح أصلاً عبر `*`).
2. **إضافة `Content-Signal`** للإعلان الصريح عن الموافقة على الـ AI.
3. **(اختياري) حظر Bytespider** — زاحف عدواني وفايدته قليلة للسوق المصري، بس مش ضروري.

### الأهم (مش من robots — من السياق)
الوصول التقني ممتاز، لكن فاكر: **سلطة العلامة** (Google Business Profile، منشن، سوشيال) هي اللي محتاجة شغل — الزواحف بتقدر توصل، بس لسه محتاج الـ AI «يعرف» البراند (شوف تقرير GEO الكامل).

---

*الخلاصة: من ناحية وصول زواحف الـ AI، الموقع في أفضل حالة (100/100). الدومين الجديد حلّ مشكلة حظر Googlebot اللي كانت على الدومين المؤقت.*
