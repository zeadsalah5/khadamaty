@extends('layouts.app')

{{--
    تتبّع الطلب /track — المرحلة 6C. بالرقم + الموبايل سوا (من غير كشف بيانات لحد تاني).
    $order (null أو نتيجة البحث)
--}}
@section('content')
    <section class="mx-auto max-w-2xl px-4 pt-10 pb-16 md:pb-20">
        <h1 class="text-2xl font-extrabold md:text-3xl">تتبّع طلبك</h1>
        <p class="mt-2 text-muted">اكتب رقم الطلب ورقم الموبايل اللي طلبت بيه عشان تشوف حالة الطلب.</p>

        @if (session('track_info'))
            <div class="mt-4 rounded-xl border border-sky-200 bg-sky-50 px-4 py-3 text-sm font-semibold text-sky-800">{{ session('track_info') }}</div>
        @endif
        @if (session('track_error'))
            <div class="mt-4 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm font-semibold text-red-700">{{ session('track_error') }}</div>
        @endif

        <form method="POST" action="{{ route('order.track.post') }}" class="mt-6 rounded-2xl border border-line bg-white p-5 md:p-6">
            @csrf
            <div class="grid gap-4 sm:grid-cols-2">
                <div>
                    <label for="order_number" class="mb-1.5 block text-sm font-semibold">رقم الطلب</label>
                    <input id="order_number" type="text" name="order_number" value="{{ old('order_number') }}" required
                           placeholder="KH-xxxxxx-xxxx" dir="ltr"
                           class="w-full rounded-xl border border-line px-4 py-2.5 text-right focus:border-brand focus:outline-none focus:ring-2 focus:ring-brand/30 @error('order_number') border-red-400 @enderror">
                    @error('order_number')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label for="phone" class="mb-1.5 block text-sm font-semibold">رقم الموبايل</label>
                    <input id="phone" type="tel" name="phone" required inputmode="numeric" autocomplete="tel" placeholder="01xxxxxxxxx" dir="ltr"
                           class="w-full rounded-xl border border-line px-4 py-2.5 text-right focus:border-brand focus:outline-none focus:ring-2 focus:ring-brand/30 @error('phone') border-red-400 @enderror">
                    @error('phone')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                </div>
            </div>
            <button type="submit" class="mt-5 rounded-full bg-brand px-7 py-3 font-bold text-white transition-colors hover:bg-brand-600">تتبّع الطلب</button>
        </form>

        @if ($order)
            <div class="mt-8">
                <h2 class="mb-3 text-lg font-bold">حالة الطلب</h2>
                @include('shop.partials.order-summary', ['order' => $order])
            </div>
        @endif
    </section>
@endsection
