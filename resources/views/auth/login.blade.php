@extends('layouts.app')

{{-- تسجيل دخول العميل (المرحلة 6D) — بالموبايل أو الإيميل. صفحة noindex. --}}
@section('content')
    <section class="mx-auto max-w-md px-4 pt-10 pb-16 md:pb-20">
        <h1 class="text-2xl font-extrabold md:text-3xl">تسجيل الدخول</h1>
        <p class="mt-2 text-muted">ادخل بالموبايل أو الإيميل عشان تشوف طلباتك ومفضّلتك.</p>

        @if (session('account_success'))
            <div class="mt-4 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-semibold text-emerald-700">{{ session('account_success') }}</div>
        @endif

        <form method="POST" action="{{ route('login.post') }}" class="mt-6 space-y-4 rounded-2xl border border-line bg-white p-5 md:p-6">
            @csrf
            <div>
                <label for="identifier" class="mb-1.5 block text-sm font-semibold">الموبايل أو الإيميل</label>
                <input id="identifier" type="text" name="identifier" value="{{ old('identifier') }}" required autofocus
                       autocomplete="username" spellcheck="false" autocapitalize="off"
                       class="w-full rounded-xl border border-line px-4 py-2.5 focus:border-brand focus:outline-none focus:ring-2 focus:ring-brand/30 @error('identifier') border-red-400 @enderror">
                @error('identifier')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
            </div>
            <div>
                <label for="password" class="mb-1.5 block text-sm font-semibold">كلمة السر</label>
                <input id="password" type="password" name="password" required autocomplete="current-password"
                       class="w-full rounded-xl border border-line px-4 py-2.5 focus:border-brand focus:outline-none focus:ring-2 focus:ring-brand/30 @error('password') border-red-400 @enderror">
                @error('password')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
            </div>
            <label class="flex items-center gap-2 text-sm text-muted">
                <input type="checkbox" name="remember" value="1" class="rounded border-line text-brand focus:ring-brand/30">
                فكّرني
            </label>
            <button type="submit" class="w-full rounded-full bg-brand px-7 py-3 font-bold text-white transition-colors hover:bg-brand-600">دخول</button>
        </form>

        <p class="mt-5 text-center text-sm text-muted">
            لسه معندكش حساب؟ <a href="{{ route('register') }}" class="font-semibold text-brand hover:underline">اعمل حساب جديد</a>
        </p>
    </section>
@endsection
