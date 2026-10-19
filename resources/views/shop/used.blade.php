@extends('layouts.app')

{{--
    /used — صفحة محتوى ثابتة قابلة للفهرسة عن التكييفات المستعملة.
    محتوى فريد: شرح درجات الحالة + الضمان + الفحص. (مسودة TODO يعدّلها صاحب الموقع).
    $products: التكييفات المستعملة المنشورة.
--}}
@php
    // درجات الحالة — مسودة TODO (يعدّلها صاحب الموقع من الداشبورد لاحقاً).
    $grades = [
        ['grade' => 'ممتاز', 'desc' => 'شبه جديد، استعمال خفيف جداً ومفيش أي خدوش واضحة. أداء تبريد كامل.'],
        ['grade' => 'جيد جداً', 'desc' => 'استعمال بسيط وأثر زمن محدود، اتعمله صيانة شاملة وبيشتغل بكفاءة عالية.'],
        ['grade' => 'جيد', 'desc' => 'علامات استعمال ظاهرة في الشكل، لكن أداء التبريد سليم بعد الفحص والصيانة.'],
    ];
@endphp

@section('content')
    {{-- ============ الهيرو ============ --}}
    <x-breadcrumbs :items="$breadcrumbs" />

    <header class="mx-auto max-w-6xl px-4 pt-6 pb-10 md:pb-12">
        <span class="inline-block rounded-full bg-amber-100 px-3 py-1 text-sm font-bold text-amber-800">تكييفات مستعملة</span>
        <h1 class="mt-3 text-3xl font-extrabold md:text-4xl">{{ $title }}</h1>
        <p class="mt-3 max-w-2xl text-lg leading-relaxed text-muted">{{ $subtitle }}</p>
    </header>

    {{-- ============ ليه تشتري مستعمل من خدماتي ============ --}}
    <section class="mx-auto max-w-6xl px-4">
        <div class="grid gap-4 sm:grid-cols-3">
            @foreach ([
                ['t' => 'فحص فني قبل العرض', 'd' => 'كل جهاز بيتفحص من فني متخصص، وبنكتب تقرير بحالته والأعمال اللي اتعملت.'],
                ['t' => 'درجة حالة واضحة', 'd' => 'بنحدد درجة الحالة بصراحة عشان تعرف بالظبط بتشتري إيه.'],
                ['t' => 'ضمان على الجهاز', 'd' => 'بندّي ضمان على التشغيل حسب حالة كل جهاز — مكتوب في تفاصيل المنتج.'],
            ] as $card)
                <div class="rounded-2xl border border-line bg-white p-5">
                    <span class="flex h-11 w-11 items-center justify-center rounded-xl bg-brand-50 text-brand" aria-hidden="true">
                        <svg viewBox="0 0 24 24" class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 12l2 2 4-4"/><path d="M12 3 4 6v6c0 5 3.5 7.5 8 9 4.5-1.5 8-4 8-9V6z"/></svg>
                    </span>
                    <h2 class="mt-3 text-lg font-bold">{{ $card['t'] }}</h2>
                    <p class="mt-1 leading-relaxed text-muted">{{ $card['d'] }}</p>
                </div>
            @endforeach
        </div>
    </section>

    {{-- ============ درجات الحالة ============ --}}
    <section class="mx-auto mt-12 max-w-6xl px-4">
        <div class="max-w-2xl">
            <h2 class="text-2xl font-extrabold md:text-3xl">إيه معنى درجات الحالة؟</h2>
            <p class="mt-2 leading-relaxed text-muted">بنصنّف كل جهاز مستعمل حسب حالته الفعلية بعد الفحص، عشان تختار اللي يناسب ميزانيتك واحتياجك.</p>
        </div>
        <div class="mt-6 overflow-hidden rounded-2xl border border-line">
            @foreach ($grades as $g)
                <div class="flex flex-col gap-1 border-b border-line bg-white px-5 py-4 last:border-b-0 sm:flex-row sm:items-center sm:gap-5">
                    <span class="shrink-0 rounded-full bg-amber-100 px-4 py-1 text-center text-sm font-bold text-amber-800 sm:w-32">{{ $g['grade'] }}</span>
                    <p class="leading-relaxed text-ink/90">{{ $g['desc'] }}</p>
                </div>
            @endforeach
        </div>
        <p class="mt-3 text-sm text-muted">ملاحظة: التصنيف تقديري حسب فحص الفني، والصور الحقيقية للجهاز موجودة في صفحته. <span class="font-semibold">(TODO: صاحب الموقع يراجع صياغة الدرجات.)</span></p>
    </section>

    {{-- ============ الضمان ============ --}}
    <section class="mx-auto mt-12 max-w-6xl px-4">
        <div class="rounded-2xl bg-navy p-6 text-white md:p-8">
            <h2 class="text-2xl font-extrabold">الضمان على الأجهزة المستعملة</h2>
            <p class="mt-3 max-w-3xl leading-relaxed text-white/80">
                بنوفّر ضمان تشغيل على كل جهاز مستعمل حسب حالته، ومدته مكتوبة في تفاصيل كل منتج. الضمان بيغطّي أعطال التشغيل الأساسية
                ومبيغطّيش سوء الاستخدام أو التركيب من حد تاني.
                <span class="font-semibold text-amber-300">(TODO: صاحب الموقع يحدد مدة الضمان وشروطه بالظبط.)</span>
            </p>
            <a href="https://wa.me/{{ site('whatsapp') }}" target="_blank" rel="noopener"
               class="mt-5 inline-flex items-center gap-2 rounded-full bg-white px-6 py-3 font-bold text-navy transition-colors hover:bg-white/90">اسأل عن الضمان</a>
        </div>
    </section>

    {{-- ============ الأجهزة المتاحة ============ --}}
    <section class="mx-auto mt-12 max-w-6xl px-4 pb-16 md:pb-20">
        <h2 class="mb-5 text-2xl font-extrabold">تكييفات مستعملة متاحة دلوقتي</h2>
        @if ($products->isNotEmpty())
            <div class="grid grid-cols-2 gap-4 sm:gap-5 lg:grid-cols-4">
                @foreach ($products as $product)
                    <x-product-card :product="$product" />
                @endforeach
            </div>
        @else
            <div class="rounded-2xl border border-dashed border-line bg-surface px-4 py-16 text-center">
                <p class="text-lg text-muted">مفيش أجهزة مستعملة متاحة حالياً — بنجدّد المعروض باستمرار.</p>
                <a href="https://wa.me/{{ site('whatsapp') }}" target="_blank" rel="noopener"
                   class="mt-5 inline-block rounded-full bg-accent px-6 py-3 font-bold text-white transition-colors hover:bg-accent-600">سجّل طلبك على واتساب</a>
            </div>
        @endif
    </section>
@endsection
