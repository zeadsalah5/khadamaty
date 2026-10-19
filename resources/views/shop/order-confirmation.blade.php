@extends('layouts.app')

{{--
    صفحة تأكيد الطلب — المرحلة 6C. متاحة للطلب اللي اتعمل دلوقتي بس (session).
    $order (محمّل معاه items)
--}}
@section('content')
    <section class="mx-auto max-w-2xl px-4 pt-10 pb-16 md:pb-20">
        @php($awaiting = in_array($order->payment_status, ['pending_payment', 'failed'], true))
        @php($canRetry = $awaiting && ! ($order->payment_due_at && $order->payment_due_at->isPast()))

        @if ($order->payment_status === 'paid')
            {{-- دفع أونلاين مؤكّد --}}
            <div class="text-center">
                <span class="mx-auto flex h-16 w-16 items-center justify-center rounded-full bg-emerald-100 text-emerald-600" aria-hidden="true">
                    <svg viewBox="0 0 24 24" class="h-9 w-9" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6 9 17l-5-5"/></svg>
                </span>
                <h1 class="mt-4 text-2xl font-extrabold md:text-3xl">تم الدفع بنجاح!</h1>
                <p class="mt-2 leading-relaxed text-muted">استلمنا دفعتك وأكّدنا طلبك. هنتواصل معاك على <span class="font-semibold text-ink" dir="ltr">{{ $order->customer_phone }}</span> لترتيب التوصيل.</p>
            </div>
        @elseif ($awaiting)
            {{-- دفع أونلاين لسه ماتمّش --}}
            <div class="text-center">
                <span class="mx-auto flex h-16 w-16 items-center justify-center rounded-full bg-amber-100 text-amber-600" aria-hidden="true">
                    <svg viewBox="0 0 24 24" class="h-9 w-9" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/></svg>
                </span>
                <h1 class="mt-4 text-2xl font-extrabold md:text-3xl">طلبك مستنّي الدفع</h1>
                <p class="mt-2 leading-relaxed text-muted">حجزنا طلبك، بس الدفع لسه ماكملش. لو خلّصت الدفع، الصفحة دي هتتحدّث خلال دقايق. أو تقدر تعيد المحاولة.</p>
            </div>

            @if (session('payment_error'))
                <div class="mt-4 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm font-semibold text-red-700">{{ session('payment_error') }}</div>
            @endif

            @if ($canRetry)
                <div class="mt-5 text-center">
                    <a href="{{ route('payment.retry', $order) }}" class="inline-block rounded-full bg-accent px-6 py-3 font-bold text-white transition-colors hover:bg-accent-600">إتمام الدفع الآن</a>
                </div>
            @else
                <div class="mt-4 rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-900">انتهت مهلة الدفع لهذا الطلب. ابدأ طلب جديد أو كلّمنا على واتساب.</div>
            @endif
        @else
            {{-- الدفع عند الاستلام (COD) --}}
            <div class="text-center">
                <span class="mx-auto flex h-16 w-16 items-center justify-center rounded-full bg-emerald-100 text-emerald-600" aria-hidden="true">
                    <svg viewBox="0 0 24 24" class="h-9 w-9" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6 9 17l-5-5"/></svg>
                </span>
                <h1 class="mt-4 text-2xl font-extrabold md:text-3xl">تم استلام طلبك!</h1>
                <p class="mt-2 leading-relaxed text-muted">هنتواصل معاك على <span class="font-semibold text-ink" dir="ltr">{{ $order->customer_phone }}</span> لتأكيد الطلب والميعاد. الدفع عند الاستلام.</p>
            </div>
        @endif

        <div class="mt-8">
            @include('shop.partials.order-summary', ['order' => $order])
        </div>

        <div class="mt-6 rounded-2xl border border-line bg-surface p-5 text-sm">
            <p class="text-muted">تقدر تتابع حالة طلبك في أي وقت برقم الطلب والموبايل.</p>
            <a href="{{ route('order.track') }}" class="mt-2 inline-block font-bold text-brand hover:underline">صفحة تتبّع الطلب ←</a>
        </div>

        <div class="mt-6 flex flex-wrap justify-center gap-3">
            <a href="{{ route('shop.index') }}" class="rounded-full bg-brand px-6 py-3 font-bold text-white transition-colors hover:bg-brand-600">مواصلة التسوّق</a>
            <a href="https://wa.me/{{ site('whatsapp') }}?text={{ rawurlencode('استفسار عن طلب رقم ' . $order->order_number) }}" target="_blank" rel="noopener"
               class="rounded-full border border-line px-6 py-3 font-bold transition-colors hover:border-accent hover:text-accent">تواصل على واتساب</a>
        </div>
    </section>
@endsection
