@extends('layouts.app')

{{-- إنشاء حساب عميل (المرحلة 6D) — الموبايل أو الإيميل (واحد على الأقل). صفحة noindex. --}}
@section('content')
    <section class="mx-auto max-w-md px-4 pt-10 pb-16 md:pb-20">
        <h1 class="text-2xl font-extrabold md:text-3xl">إنشاء حساب</h1>
        <p class="mt-2 text-muted">حساب اختياري عشان تتابع طلباتك وتحفظ مفضّلتك. تقدر تطلب كضيف برضه.</p>

        <form method="POST" action="{{ route('register.post') }}" class="mt-6 space-y-4 rounded-2xl border border-line bg-white p-5 md:p-6">
            @csrf
            {{-- فخ السبام (مخفي) --}}
            <input type="text" name="website" value="" tabindex="-1" autocomplete="off" class="hidden" aria-hidden="true">

            <div>
                <label for="name" class="mb-1.5 block text-sm font-semibold">الاسم</label>
                <input id="name" type="text" name="name" value="{{ old('name') }}" required autofocus autocomplete="name"
                       class="w-full rounded-xl border border-line px-4 py-2.5 focus:border-brand focus:outline-none focus:ring-2 focus:ring-brand/30 @error('name') border-red-400 @enderror">
                @error('name')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
            </div>
            <div>
                <label for="phone" class="mb-1.5 block text-sm font-semibold">الموبايل <span class="font-normal text-muted">(أو سيبه واملأ الإيميل)</span></label>
                <input id="phone" type="tel" name="phone" value="{{ old('phone') }}" inputmode="numeric" autocomplete="tel" placeholder="01xxxxxxxxx" dir="ltr"
                       class="w-full rounded-xl border border-line px-4 py-2.5 text-right focus:border-brand focus:outline-none focus:ring-2 focus:ring-brand/30 @error('phone') border-red-400 @enderror">
                @error('phone')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
            </div>
            <div>
                <label for="email" class="mb-1.5 block text-sm font-semibold">الإيميل <span class="font-normal text-muted">(أو سيبه واملأ الموبايل)</span></label>
                <input id="email" type="email" name="email" value="{{ old('email') }}" autocomplete="email" spellcheck="false" autocapitalize="off" dir="ltr"
                       class="w-full rounded-xl border border-line px-4 py-2.5 text-right focus:border-brand focus:outline-none focus:ring-2 focus:ring-brand/30 @error('email') border-red-400 @enderror">
                @error('email')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
            </div>
            <div>
                <label for="password" class="mb-1.5 block text-sm font-semibold">كلمة السر</label>
                <input id="password" type="password" name="password" required autocomplete="new-password" minlength="8"
                       class="w-full rounded-xl border border-line px-4 py-2.5 focus:border-brand focus:outline-none focus:ring-2 focus:ring-brand/30 @error('password') border-red-400 @enderror">
                @error('password')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
            </div>
            <div>
                <label for="password_confirmation" class="mb-1.5 block text-sm font-semibold">تأكيد كلمة السر</label>
                <input id="password_confirmation" type="password" name="password_confirmation" required autocomplete="new-password"
                       class="w-full rounded-xl border border-line px-4 py-2.5 focus:border-brand focus:outline-none focus:ring-2 focus:ring-brand/30">
            </div>
            <button type="submit" class="w-full rounded-full bg-brand px-7 py-3 font-bold text-white transition-colors hover:bg-brand-600">إنشاء الحساب</button>
        </form>

        <p class="mt-5 text-center text-sm text-muted">
            عندك حساب بالفعل؟ <a href="{{ route('login') }}" class="font-semibold text-brand hover:underline">سجّل دخول</a>
        </p>
    </section>
@endsection
