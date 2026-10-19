@extends('layouts.app')

{{--
    صفحة "مين إحنا".
    محتوى تعريفي حقيقي — من غير أرقام عملاء أو إنجازات مفبركة.
    متغيرات: $breadcrumbs
--}}

@section('content')
    <x-page-hero
        title="مين إحنا"
        subtitle="«{{ site('name') }}» فريق فنيين متخصصين في صيانة وتركيب وإصلاح التكييفات والأجهزة في {{ site('areas_label') }} — شغل نضيف، مواعيد محترمة، والدفع بعد ما تتأكد إن كل حاجة تمام."
        :breadcrumbs="$breadcrumbs" />

    {{-- قصتنا / فكرتنا --}}
    <section class="mx-auto max-w-3xl px-4 pb-4">
        <div class="space-y-4 text-ink/90 leading-loose">
            <p>
                بدأنا «{{ site('name') }}» بفكرة بسيطة: صيانة التكييف المفروض تكون سهلة ومريحة،
                مش مشوار ورا فني متأخر ولا أسعار بتتغير في آخر لحظة. عشان كده بنشتغل بنظام واضح —
                تحجز، الفني يوصلك في المعاد، يعاين ويقولك السعر قبل ما يبدأ، وتدفع بعد ما تتأكد.
            </p>
            <p>
                إحنا مش توكيل لماركة معيّنة، وبنصلّح كل الماركات. فنيينا متدربين على أنواع مختلفة من
                التكييفات (سبليت، شباك، كونسيلد) والأجهزة المنزلية، وبيستخدموا قطع غيار مناسبة لكل جهاز.
            </p>
        </div>
    </section>

    {{-- مبادئنا --}}
    <section class="mx-auto max-w-6xl px-4 py-12 md:py-16">
        <h2 class="text-2xl md:text-3xl font-extrabold mb-6">إزاي بنفكّر في الشغل</h2>
        <div class="grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
            @foreach ([
                ['t' => 'شفافية في السعر', 'd' => 'بتعرف السعر قبل ما نبدأ. لو ظهر شغل زيادة وقت المعاينة، بنقولك الأول وإنت تقرر.'],
                ['t' => 'نظافة واحترام للمكان', 'd' => 'الفني بيشتغل وهو واخد باله من بيتك، وبيسيب المكان زي ما لقاه أو أنضف.'],
                ['t' => 'الدفع بعد الشغل', 'd' => 'مفيش مقدم. بتدفع بعد ما تجرّب وتتأكد إن الجهاز رجع يشتغل زي الفل.'],
                ['t' => 'مواعيد بنلتزم بيها', 'd' => 'بنحدد معاد واقعي وبنحترمه. ولو حصل ظرف بنكلّمك ونتفق من جديد.'],
                ['t' => 'كل الماركات', 'd' => 'مش بنفرّق بين ماركة وماركة — قولنا النوع والموديل وإحنا نظبّطك.'],
                ['t' => 'ضمان على الشغل', 'd' => 'بنقف ورا اللي بنعمله، وفيه ضمان على الصيانة والإصلاح حسب نوع الخدمة.'],
            ] as $v)
                <div class="rounded-2xl border border-line bg-white p-6">
                    <div class="w-11 h-11 rounded-xl bg-brand-50 text-brand flex items-center justify-center mb-4">
                        <svg viewBox="0 0 24 24" class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M20 6 9 17l-5-5"/></svg>
                    </div>
                    <h3 class="font-bold text-lg mb-1.5">{{ $v['t'] }}</h3>
                    <p class="text-muted text-sm leading-relaxed">{{ $v['d'] }}</p>
                </div>
            @endforeach
        </div>
    </section>

    {{-- بنعمل إيه + مناطق --}}
    <section class="bg-surface">
        <div class="mx-auto max-w-6xl px-4 py-12 md:py-16 grid gap-10 md:grid-cols-2">
            <div>
                <h2 class="text-2xl md:text-3xl font-extrabold mb-4">بنعمل إيه بالظبط</h2>
                <ul class="space-y-3">
                    @foreach (site('booking_services') as $srv)
                        <li class="flex items-center gap-3">
                            <span class="w-2 h-2 rounded-full bg-brand shrink-0" aria-hidden="true"></span>
                            <span class="font-medium">{{ $srv }}</span>
                        </li>
                    @endforeach
                </ul>
                <a href="{{ route('services.index') }}"
                   class="inline-flex items-center gap-1.5 mt-6 text-brand font-bold hover:gap-2.5 transition-all">
                    اعرف تفاصيل كل خدمة
                    <svg viewBox="0 0 24 24" class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m15 18-6-6 6-6"/></svg>
                </a>
            </div>
            <div>
                <h2 class="text-2xl md:text-3xl font-extrabold mb-4">بنغطي فين</h2>
                <p class="text-muted mb-4 leading-relaxed">بنخدم {{ site('areas_label') }} والمناطق المحيطة. مش لاقي منطقتك؟ كلّمنا ونشوف.</p>
                <ul class="flex flex-wrap gap-2.5">
                    @foreach (site('areas') as $area)
                        <li class="bg-white border border-line rounded-full px-4 py-2 text-sm font-medium">{{ $area }}</li>
                    @endforeach
                </ul>
            </div>
        </div>
    </section>

    {{-- CTA --}}
    <section class="mx-auto max-w-6xl px-4 py-14 md:py-20">
        <div class="rounded-3xl bg-navy text-white p-8 md:p-12 text-center">
            <h2 class="text-2xl md:text-3xl font-extrabold mb-3">جاهزين نساعدك</h2>
            <p class="text-white/70 mb-7 max-w-xl mx-auto leading-relaxed">
                احجز فني دلوقتي أو كلّمنا، وهنتفق على كل حاجة قبل ما نبدأ.
            </p>
            <div class="flex flex-wrap justify-center gap-3">
                <a href="{{ url('/') }}#booking"
                   class="inline-flex items-center px-7 py-3.5 rounded-full bg-brand hover:bg-brand-600 font-bold transition-colors">
                    احجز فني دلوقتي
                </a>
                <a href="tel:{{ site('phone_tel') }}"
                   class="inline-flex items-center px-7 py-3.5 rounded-full border border-white/25 hover:border-white font-bold transition-colors">
                    اتصل بينا
                </a>
            </div>
        </div>
    </section>
@endsection
