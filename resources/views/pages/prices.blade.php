@extends('layouts.app')

{{--
    صفحة الأسعار والباقات.
    الباقات من الداشبورد (Catalog::packages)، وجدول الخدمات من حقل «السعر» (price_note)
    للخدمات المنشورة. مفيش أرقام في الكود: الخدمة اللي سعرها فاضي أو فيه TODO مبتظهرش،
    ولو مفيش ولا سعر الجدول كله بيتخفى.
    متغيرات: $breadcrumbs, $priceRows (Collection من Service)
--}}

@section('content')
    <x-page-hero
        title="الأسعار والباقات"
        subtitle="أسعار واضحة من غير مفاجآت. بنديك سعر مبدئي وقت الحجز، وبيتأكد وقت المعاينة قبل أي شغل — والدفع بعد ما تتأكد إن كل حاجة تمام."
        :breadcrumbs="$breadcrumbs" />

    {{-- إزاي بنحسب السعر --}}
    <section class="mx-auto max-w-6xl px-4 pb-4">
        <div class="grid gap-4 sm:grid-cols-3">
            @foreach ([
                ['t' => 'سعر مبدئي عند الحجز', 'd' => 'أول ما تحجز بنقولك على نطاق سعر تقريبي حسب الخدمة والجهاز.'],
                ['t' => 'تأكيد عند المعاينة', 'd' => 'الفني يعاين على الطبيعة ويقولك السعر النهائي قبل ما يبدأ.'],
                ['t' => 'الدفع بعد الشغل', 'd' => 'مبتدفعش غير لما تتأكد إن الخدمة اتعملت تمام.'],
            ] as $step)
                <div class="rounded-2xl border border-line bg-white p-6">
                    <h2 class="font-bold text-lg mb-1.5">{{ $step['t'] }}</h2>
                    <p class="text-muted text-sm leading-relaxed">{{ $step['d'] }}</p>
                </div>
            @endforeach
        </div>
    </section>

    {{-- الباقات --}}
    <section class="mx-auto max-w-6xl px-4 py-10 md:py-14">
        <h2 class="text-2xl md:text-3xl font-extrabold mb-6">باقات الصيانة والتركيب</h2>
        <div class="grid gap-6 md:grid-cols-3 items-stretch">
            @foreach (\App\Support\Catalog::packages() as $pkg)
                <div class="relative rounded-2xl border p-7 flex flex-col
                            {{ $pkg['featured'] ? 'border-brand shadow-xl shadow-brand/10' : 'border-line' }}">
                    @if ($pkg['featured'])
                        <span class="absolute -top-3 end-7 bg-accent text-white text-xs font-bold px-3 py-1 rounded-full">الأكثر طلباً</span>
                    @endif
                    <h3 class="text-xl font-bold mb-1">{{ $pkg['name'] }}</h3>
                    <div class="flex items-end gap-1.5 my-4">
                        <span class="text-4xl font-extrabold text-brand">{{ $pkg['price'] }}</span>
                        <span class="text-muted text-sm mb-1">{{ $pkg['unit'] }}</span>
                    </div>
                    <ul class="space-y-3 text-sm mb-7 flex-1">
                        @foreach ($pkg['features'] as $feature)
                            <li class="flex items-start gap-2.5">
                                <svg viewBox="0 0 24 24" class="w-5 h-5 shrink-0 text-accent mt-0.5" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M20 6 9 17l-5-5"/></svg>
                                <span>{{ $feature }}</span>
                            </li>
                        @endforeach
                    </ul>
                    <a href="{{ url('/') }}#booking"
                       class="block text-center py-3 rounded-full font-bold transition-colors
                              {{ $pkg['featured'] ? 'bg-brand hover:bg-brand-600 text-white' : 'border border-line hover:border-brand hover:text-brand' }}">
                        احجز الباقة
                    </a>
                </div>
            @endforeach
        </div>
    </section>

    {{-- جدول أسعار الخدمات (إرشادي) — من الداشبورد --}}
    @if ($priceRows->isNotEmpty())
        <section class="mx-auto max-w-6xl px-4 pb-6">
            <h2 class="text-2xl md:text-3xl font-extrabold mb-2">أسعار الخدمات (إرشادية)</h2>
            <p class="text-muted mb-6 max-w-2xl leading-relaxed">
                دي أسعار مبدئية للاسترشاد بس. السعر النهائي بيتحدد بعد المعاينة حسب حالة الجهاز وحجم الشغل.
            </p>

            <div class="overflow-x-auto rounded-2xl border border-line">
                <table class="w-full text-start border-collapse min-w-[32rem]">
                    <caption class="sr-only">جدول الأسعار الإرشادية للخدمات</caption>
                    <thead>
                        <tr class="bg-surface text-sm text-ink">
                            <th scope="col" class="text-start font-bold px-5 py-3.5">الخدمة</th>
                            <th scope="col" class="text-start font-bold px-5 py-3.5">السعر</th>
                            <th scope="col" class="text-start font-bold px-5 py-3.5 hidden sm:table-cell">المدة التقريبية</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-line">
                        @foreach ($priceRows as $row)
                            <tr class="bg-white">
                                <th scope="row" class="text-start font-semibold px-5 py-4">
                                    <a href="{{ url('/services/' . $row->slug) }}" class="hover:text-brand transition-colors">{{ $row->name_ar }}</a>
                                </th>
                                <td class="px-5 py-4 font-bold text-brand">{{ public_note($row->price_note) }}</td>
                                <td class="px-5 py-4 text-muted text-sm hidden sm:table-cell">{{ public_note($row->duration_note) ?? '—' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </section>
    @endif

    {{-- CTA --}}
    <section class="mx-auto max-w-6xl px-4 pb-16 md:pb-20">
        <div class="rounded-2xl bg-surface border border-line p-7 md:p-9 flex flex-col md:flex-row md:items-center justify-between gap-5">
            <div>
                <h2 class="text-xl md:text-2xl font-extrabold mb-1">عايز سعر مظبوط لحالتك؟</h2>
                <p class="text-muted leading-relaxed">احجز معاينة أو اسأل على واتساب — بنقولك السعر التقريبي على طول.</p>
            </div>
            <div class="flex flex-wrap gap-3 shrink-0">
                <a href="{{ url('/') }}#booking"
                   class="inline-flex items-center px-6 py-3 rounded-full bg-brand hover:bg-brand-600 text-white font-bold transition-colors">
                    احجز معاينة
                </a>
                <a href="https://wa.me/{{ site('whatsapp') }}" target="_blank" rel="noopener"
                   class="inline-flex items-center px-6 py-3 rounded-full border border-line hover:border-brand hover:text-brand font-bold transition-colors">
                    اسأل على واتساب
                </a>
            </div>
        </div>
    </section>
@endsection
