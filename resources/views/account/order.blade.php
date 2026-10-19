@extends('layouts.app')

{{-- تفاصيل طلب للعميل المسجّل (المرحلة 6D). noindex. --}}
@section('content')
    <section class="mx-auto max-w-2xl px-4 pt-8 pb-16 md:pb-20">
        <x-breadcrumbs :items="$breadcrumbs" />

        <div class="mb-5 flex items-center justify-between gap-3">
            <h1 class="text-2xl font-extrabold md:text-3xl">تفاصيل الطلب</h1>
            <a href="{{ route('account.orders') }}" class="text-sm font-semibold text-brand hover:underline">رجوع لطلباتي</a>
        </div>

        @include('shop.partials.order-summary', ['order' => $order])

        <div class="mt-6 rounded-2xl border border-line bg-white p-5 text-sm md:p-6">
            <h2 class="mb-3 font-bold">بيانات التوصيل</h2>
            <dl class="space-y-2">
                <div class="flex gap-2"><dt class="text-muted">الاسم:</dt><dd class="font-medium">{{ $order->customer_name }}</dd></div>
                <div class="flex gap-2"><dt class="text-muted">الموبايل:</dt><dd class="font-medium" dir="ltr">{{ $order->customer_phone }}</dd></div>
                <div class="flex gap-2"><dt class="text-muted">المنطقة:</dt><dd class="font-medium">{{ $order->area_name }}</dd></div>
                <div class="flex gap-2"><dt class="text-muted">العنوان:</dt><dd class="font-medium">{{ $order->address }}</dd></div>
                @if ($order->notes)
                    <div class="flex gap-2"><dt class="text-muted">ملاحظات:</dt><dd class="font-medium">{{ $order->notes }}</dd></div>
                @endif
            </dl>
        </div>
    </section>
@endsection
