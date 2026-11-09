@extends('layouts.app')

{{-- المفضلة (المرحلة 6D) — تشتغل للضيف (session) والمسجّل (DB). noindex. --}}
@section('content')
    <section class="mx-auto max-w-6xl px-4 pt-8 pb-16 md:pb-20">
        <x-breadcrumbs :items="$breadcrumbs" />

        @auth('customer')
            @include('account.partials.header', ['active' => 'wishlist'])
        @else
            <div class="mb-6">
                <h1 class="text-2xl font-extrabold md:text-3xl">المفضلة</h1>
                <p class="mt-2 text-muted">
                    <a href="{{ route('login') }}" class="font-semibold text-brand hover:underline">سجّل دخول</a>
                    عشان تحتفظ بمفضّلتك على كل أجهزتك.
                </p>
            </div>
        @endauth

        @if (session('wishlist_success'))
            <div class="mb-5 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-semibold text-emerald-700">{{ session('wishlist_success') }}</div>
        @endif

        @if ($products->isEmpty())
            <div class="rounded-2xl border border-dashed border-line bg-surface p-10 text-center">
                <p class="text-muted">المفضلة فاضية. دوس على ♥ في أي منتج عشان تحفظه هنا.</p>
                <a href="{{ route('shop.index') }}" class="mt-4 inline-flex rounded-full bg-brand px-6 py-2.5 font-bold text-white transition-colors hover:bg-brand-600">تصفّح المتجر</a>
            </div>
        @else
            <div class="grid grid-cols-2 gap-4 sm:gap-5 lg:grid-cols-4">
                @foreach ($products as $product)
                    <x-product-card :product="$product" />
                @endforeach
            </div>
        @endif
    </section>
@endsection
