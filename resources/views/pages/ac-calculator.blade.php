@extends('layouts.app')

{{-- حاسبة قدرة التكييف المناسب للغرفة — أداة تفاعلية (JS) + دليل. النتيجة تقديرية. --}}

@push('schema')
    <x-schema.faq-page :items="$faqs" />
@endpush

@section('content')

<x-breadcrumbs :items="$breadcrumbs" />

<header class="mx-auto max-w-4xl px-4 pt-6 pb-8 text-center">
    <span class="inline-flex items-center gap-2 rounded-full bg-brand-50 text-brand font-bold text-sm px-4 py-1.5 mb-4">
        أداة مجانية
    </span>
    <h1 class="text-3xl md:text-4xl font-extrabold mb-3">حاسبة قدرة التكييف المناسب للغرفة</h1>
    <p class="text-muted text-lg leading-relaxed max-w-2xl mx-auto">
        أدخل أبعاد غرفتك ودرجة تعرّضها للشمس، واعرف قدرة التكييف المناسبة <strong class="text-ink">بالحصان</strong> تقريباً —
        عشان متشتريش أكبر من اللازم (كهربا زيادة) ولا أصغر (تبريد ضعيف).
    </p>
</header>

{{-- ============ الحاسبة ============ --}}
<section class="mx-auto max-w-4xl px-4 pb-6">
    <form class="rounded-3xl border border-line bg-white p-6 md:p-8 shadow-sm" data-ac-calc novalidate
          aria-label="حاسبة قدرة التكييف">
        <div class="grid gap-5 sm:grid-cols-2">
            <div>
                <label for="acLength" class="block font-semibold mb-1.5">طول الغرفة (متر)</label>
                <input id="acLength" name="length" type="number" inputmode="decimal" min="1" max="50" step="0.1"
                       autocomplete="off" placeholder="مثال: 5"
                       class="w-full rounded-xl border border-line px-4 py-3 focus:border-brand focus:ring-2 focus:ring-brand/20 outline-none">
            </div>
            <div>
                <label for="acWidth" class="block font-semibold mb-1.5">عرض الغرفة (متر)</label>
                <input id="acWidth" name="width" type="number" inputmode="decimal" min="1" max="50" step="0.1"
                       autocomplete="off" placeholder="مثال: 4"
                       class="w-full rounded-xl border border-line px-4 py-3 focus:border-brand focus:ring-2 focus:ring-brand/20 outline-none">
            </div>
            <div>
                <label for="acHeight" class="block font-semibold mb-1.5">ارتفاع السقف (متر)</label>
                <input id="acHeight" name="height" type="number" inputmode="decimal" min="2" max="6" step="0.1"
                       autocomplete="off" placeholder="الافتراضي: 2.8"
                       class="w-full rounded-xl border border-line px-4 py-3 focus:border-brand focus:ring-2 focus:ring-brand/20 outline-none">
            </div>
            <div>
                <label for="acSun" class="block font-semibold mb-1.5">تعرّض الغرفة للشمس</label>
                <select id="acSun" name="sun"
                        class="w-full rounded-xl border border-line px-4 py-3 bg-white focus:border-brand focus:ring-2 focus:ring-brand/20 outline-none">
                    <option value="low">قليل (مظلّلة / ناحية شمال)</option>
                    <option value="medium" selected>متوسط</option>
                    <option value="high">عالي (شمس مباشرة / ناصية / آخر دور)</option>
                </select>
            </div>
            <div class="sm:col-span-2">
                <label for="acPeople" class="block font-semibold mb-1.5">عدد الأفراد عادةً <span class="text-muted font-normal">(اختياري)</span></label>
                <input id="acPeople" name="people" type="number" inputmode="numeric" min="1" max="20" step="1"
                       autocomplete="off" placeholder="مثال: 3"
                       class="w-full rounded-xl border border-line px-4 py-3 focus:border-brand focus:ring-2 focus:ring-brand/20 outline-none">
            </div>
        </div>

        {{-- رسالة خطأ تظهر عند إدخال ناقص --}}
        <p class="mt-3 text-brand font-semibold text-sm hidden" data-ac-error role="alert" aria-live="polite"></p>

        <div class="mt-6 flex flex-wrap gap-3">
            <button type="submit"
                    class="inline-flex items-center justify-center px-7 py-3 rounded-full bg-brand hover:bg-brand-600 text-white font-extrabold text-lg transition-colors">
                احسب القدرة
            </button>
            <button type="reset" data-ac-reset
                    class="inline-flex items-center justify-center px-6 py-3 rounded-full border border-line text-ink font-bold hover:border-brand hover:text-brand transition-colors">
                تفريغ
            </button>
        </div>
    </form>

    {{-- ============ النتيجة ============ --}}
    <div class="mt-5 rounded-3xl border border-accent/30 bg-accent/5 p-6 md:p-8 hidden" data-ac-result
         role="region" aria-live="polite" aria-label="نتيجة الحساب">
        <div class="flex flex-wrap items-end gap-x-6 gap-y-2">
            <div>
                <p class="text-muted font-semibold mb-1">القدرة المناسبة تقريباً</p>
                <p class="text-4xl md:text-5xl font-extrabold text-accent-600" data-ac-hp>—</p>
            </div>
            <div class="text-muted">
                <p>الحِمل التقريبي: <span class="font-bold text-ink" data-ac-btu>—</span></p>
                <p>مساحة الغرفة: <span class="font-bold text-ink" data-ac-area>—</span></p>
            </div>
        </div>
        <p class="mt-4 text-sm text-ink leading-relaxed" data-ac-note></p>
        <div class="mt-5 flex flex-wrap gap-3">
            <a href="{{ url('/') }}#booking" data-service="تركيب تكييف"
               class="inline-flex items-center px-6 py-3 rounded-full bg-brand hover:bg-brand-600 text-white font-bold transition-colors">احجز تركيب</a>
            <a href="/services/ac-installation"
               class="inline-flex items-center px-6 py-3 rounded-full border border-line text-ink font-bold hover:border-brand hover:text-brand transition-colors">تفاصيل التركيب</a>
        </div>
    </div>

    <p class="mt-4 text-xs text-muted">* النتيجة تقديرية وبتساعدك تختار الأقرب للمقاسات المتوفرة في السوق المصري. العزل وعدد النوافذ واتجاه الغرفة بيأثروا كمان — المعاينة الفنية بتأكّد الرقم الصح.</p>
</section>

{{-- ============ دليل الاستخدام والمحتوى ============ --}}
<section class="bg-surface">
    <div class="mx-auto max-w-4xl px-4 py-14 article-body">
        <h2>إزاي بتحسب الحاسبة قدرة التكييف؟</h2>
        <p>الفكرة إن كل غرفة ليها <strong>حِمل حراري</strong> لازم التكييف يتغلّب عليه. الحاسبة بتبدأ من مساحة الغرفة (الطول × العرض)، وبتعدّلها حسب عوامل بتزوّد الحِمل أو تقلّله، وبعدها بتحوّل النتيجة لأقرب قدرة بالحصان المتوفرة في السوق.</p>

        <h2>العوامل اللي بتأثر على القدرة المطلوبة</h2>
        <ul>
            <li><strong>مساحة الغرفة:</strong> أهم عامل — كل ما المساحة كبرت، الحِمل زاد.</li>
            <li><strong>ارتفاع السقف:</strong> سقف أعلى = حجم هوا أكبر محتاج تبريد.</li>
            <li><strong>تعرّض الشمس:</strong> غرفة عليها شمس مباشرة أو في آخر دور بتحتاج قدرة أعلى من غرفة مظلّلة.</li>
            <li><strong>عدد الأفراد:</strong> كل شخص بيضيف حِمل حراري بسيط، فالغرف المزدحمة بتحتاج أكتر.</li>
            <li><strong>عوامل تانية:</strong> جودة العزل، عدد وحجم النوافذ، والأجهزة اللي بتطلّع حرارة (مطبخ/أجهزة) — دي بتتراجع في المعاينة.</li>
        </ul>

        <h2>دليل سريع: المساحة ← القدرة المقترحة</h2>
        <p>كتقدير مبدئي (بسقف وتعرّض عاديين):</p>
        <table>
            <thead>
                <tr><th>مساحة الغرفة التقريبية</th><th>القدرة المقترحة</th></tr>
            </thead>
            <tbody>
                <tr><td>حتى ١٥ م²</td><td>١.٥ حصان</td></tr>
                <tr><td>١٦ – ٢٢ م²</td><td>٢.٢٥ حصان</td></tr>
                <tr><td>٢٣ – ٣٠ م²</td><td>٣ حصان</td></tr>
                <tr><td>٣١ – ٤٥ م²</td><td>٤ – ٥ حصان</td></tr>
            </tbody>
        </table>
        <p>لو محتار بين قدرتين، أو الغرفة ظروفها خاصة (آخر دور، واجهة زجاج، مطبخ مفتوح)، <a href="/blog/best-ac-types-egypt">دليل اختيار نوع التكييف</a> بيساعدك، ويفضل تاخد رأي <a href="/services/ac-installation">فني تركيب</a> قبل الشراء.</p>

        <h2>أسئلة شائعة</h2>
        @foreach ($faqs as $faq)
            <details class="group border-b border-line py-3">
                <summary class="flex items-center justify-between cursor-pointer font-bold text-ink">
                    {{ $faq['q'] }}
                    <svg class="faq-icon w-5 h-5 text-muted transition-transform shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m6 9 6 6 6-6"/></svg>
                </summary>
                <p class="mt-2 text-muted leading-relaxed">{{ $faq['a'] }}</p>
            </details>
        @endforeach

        <div class="mt-10 rounded-2xl bg-navy text-white p-6 md:p-8 text-center not-prose">
            <h2 class="text-2xl font-extrabold mb-2 text-white">عرفت القدرة؟ خلّينا نركّبها صح</h2>
            <p class="text-white/80 mb-5">التركيب السليم بيحافظ على كفاءة الجهاز وعمره. احجز فني تركيب متخصص.</p>
            <a href="{{ url('/') }}#booking" data-service="تركيب تكييف"
               class="inline-flex items-center px-7 py-3 rounded-full bg-brand hover:bg-brand-600 text-white font-extrabold transition-colors">احجز تركيب تكييف</a>
        </div>
    </div>
</section>

@endsection
