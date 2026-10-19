@extends('layouts.app')

{{--
    صفحة منتج (/shop/{category}/{product}) — المرحلة 6B.
    $product (محمّل: category.attributes, category.parent, brand, images, usedDetails)
    $canonical, $related, $areas, $breadcrumbs
--}}
@php
    use Illuminate\Support\Facades\Storage;

    $disk = Storage::disk('products');

    $images = $product->images->sortByDesc(fn ($img) => $img->is_primary ? 1 : 0)->values();

    $specs = collect($product->category?->attributes ?? [])
        ->map(function ($attr) use ($product) {
            $value = data_get($product->specs, $attr->key);
            if (blank($value)) {
                return null;
            }
            return ['label' => $attr->label_ar, 'value' => $attr->unit ? ($value . ' ' . $attr->unit) : $value];
        })
        ->filter()->values();

    $isUsed   = $product->isUsed();
    $display  = $product->displayPrice();
    $hasSale  = $product->hasSale();
    $discount = $product->discountPercent();
    $sold     = $product->isSold();
    $inStock  = $product->isInStock();

    $waText = rawurlencode('عايز أستفسر عن: ' . $product->name_ar . ' — ' . $canonical);
    $waUrl  = 'https://wa.me/' . site('whatsapp') . '?text=' . $waText;
@endphp

@push('schema')
    <x-schema.product
        :name="$product->name_ar"
        :description="$product->seo_description ?: $product->excerpt"
        :url="$canonical"
        :sku="$product->sku ?: $product->model_number"
        :image="$product->imageUrls()"
        :brand="$product->brand?->name_ar"
        :price="$product->displayPrice()"
        :condition="$product->schemaCondition()"
        :availability="$product->schemaAvailability()"
        :reviews="$reviews" />
@endpush

@section('content')
    <x-breadcrumbs :items="$breadcrumbs" />

    <section class="mx-auto max-w-6xl px-4 pt-6 pb-16 md:pb-20">
        <div class="grid gap-8 md:grid-cols-2 md:gap-12">

            {{-- ============ معرض الصور ============ --}}
            <div x-gallery>
                @if ($images->isNotEmpty())
                    <div class="overflow-hidden rounded-2xl border border-line bg-white">
                        <img id="gallery-main"
                             src="{{ $disk->url($images->first()->path) }}"
                             alt="{{ $images->first()->alt ?: $product->name_ar }}"
                             width="700" height="700" decoding="async"
                             class="aspect-square w-full object-contain">
                    </div>
                    @if ($images->count() > 1)
                        <div class="mt-3 grid grid-cols-5 gap-2">
                            @foreach ($images->take(6) as $i => $img)
                                <button type="button"
                                        class="gallery-thumb overflow-hidden rounded-lg border bg-white transition-colors {{ $i === 0 ? 'border-brand' : 'border-line hover:border-brand' }}"
                                        data-src="{{ $disk->url($img->path) }}"
                                        data-alt="{{ $img->alt ?: $product->name_ar }}"
                                        aria-label="عرض صورة {{ $i + 1 }}">
                                    <img src="{{ $disk->url($img->path) }}" alt="{{ $img->alt ?: $product->name_ar }}"
                                         width="120" height="120" loading="lazy" decoding="async" class="aspect-square w-full object-contain">
                                </button>
                            @endforeach
                        </div>
                    @endif
                @else
                    <div class="flex aspect-square w-full items-center justify-center rounded-2xl border border-dashed border-line bg-surface text-muted">لا توجد صورة</div>
                @endif
            </div>

            {{-- ============ معلومات المنتج ============ --}}
            <div>
                <div class="flex flex-wrap items-center gap-2">
                    <span class="rounded-full px-3 py-1 text-sm font-bold {{ $isUsed ? 'bg-amber-100 text-amber-800' : 'bg-sky-100 text-sky-800' }}">{{ $isUsed ? 'مستعمل' : 'جديد' }}</span>
                    @if ($product->brand)
                        <a href="{{ url('/brands/' . $product->brand->slug) }}" class="rounded-full bg-surface px-3 py-1 text-sm text-muted transition-colors hover:text-brand">{{ $product->brand->name_ar }}</a>
                    @endif
                    @if ($discount)
                        <span class="rounded-full bg-brand px-3 py-1 text-sm font-extrabold text-white">خصم {{ $discount }}%</span>
                    @endif
                </div>

                <h1 class="mt-3 text-2xl font-extrabold md:text-3xl">{{ $product->name_ar }}</h1>

                @if ($reviews->isNotEmpty())
                    <a href="#reviews" class="mt-2 inline-flex items-center gap-2 text-sm font-semibold text-ink hover:text-brand">
                        <x-shop.stars :value="$product->ratingAverage()" />
                        <span>{{ number_format($product->ratingAverage(), 1) }}</span>
                        <span class="text-muted">({{ $reviews->count() }} تقييم)</span>
                    </a>
                @endif

                @if ($product->excerpt)
                    <p class="mt-2 leading-relaxed text-muted">{{ $product->excerpt }}</p>
                @endif

                {{-- السعر --}}
                <div class="mt-5">
                    @if ($display)
                        <div class="flex items-baseline gap-3">
                            <span class="text-3xl font-extrabold text-brand">{{ number_format((float) $display) }} ج.م</span>
                            @if ($hasSale)
                                <span class="text-lg text-muted line-through">{{ number_format((float) $product->price) }} ج.م</span>
                            @endif
                        </div>
                    @else
                        <p class="text-lg text-muted">اتصل للاستفسار عن السعر</p>
                    @endif

                    <p class="mt-2 inline-flex items-center gap-1.5 text-sm font-semibold {{ $sold ? 'text-navy' : ($inStock ? 'text-emerald-600' : 'text-amber-600') }}">
                        <span class="inline-block h-2 w-2 rounded-full {{ $sold ? 'bg-navy' : ($inStock ? 'bg-emerald-500' : 'bg-amber-500') }}" aria-hidden="true"></span>
                        {{ $sold ? 'تم البيع' : ($inStock ? 'متوفر الآن' : 'غير متوفر حالياً') }}
                    </p>
                </div>

                {{-- حالة «غير متوفر» / «تم البيع» --}}
                @if ($sold || ! $inStock)
                    <div class="mt-4 rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm leading-relaxed text-amber-900">
                        @if ($sold)
                            الجهاز ده اتباع. تقدر تشوف بدائل مشابهة تحت، أو تكلّمنا نوفّرلك واحد بنفس المواصفات.
                        @else
                            المنتج ده غير متوفر حالياً. كلّمنا نعرف ميعاد توفره أو نرشّحلك بديل مناسب.
                        @endif
                    </div>
                @endif

                {{-- CTA --}}
                <div class="mt-6">
                    @if (! $sold && $inStock && $display)
                        @if (session('cart_error'))
                            <div class="mb-3 rounded-xl border border-red-200 bg-red-50 px-4 py-2.5 text-sm font-semibold text-red-700">{{ session('cart_error') }}</div>
                        @endif
                        <form method="POST" action="{{ route('cart.add') }}" class="flex flex-wrap items-center gap-3">
                            @csrf
                            <input type="hidden" name="product_id" value="{{ $product->id }}">
                            @unless ($isUsed)
                                <label for="qty" class="sr-only">الكمية</label>
                                <input id="qty" type="number" name="qty" value="1" min="1" max="{{ min(20, (int) $product->stock_qty) }}"
                                       inputmode="numeric" aria-label="الكمية"
                                       class="w-16 rounded-full border border-line px-3 py-3 text-center focus:border-brand focus:outline-none focus:ring-2 focus:ring-brand/30">
                            @endunless
                            <button type="submit"
                                    class="inline-flex items-center gap-2 rounded-full bg-brand px-7 py-3 font-bold text-white transition-colors hover:bg-brand-600">
                                <svg viewBox="0 0 24 24" class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="9" cy="21" r="1"/><circle cx="20" cy="21" r="1"/><path d="M1 1h4l2.7 13.4a2 2 0 0 0 2 1.6h9.7a2 2 0 0 0 2-1.6L23 6H6"/></svg>
                                أضف للسلة
                            </button>
                        </form>
                    @endif
                    <div class="mt-3 flex flex-wrap gap-3">
                        <x-shop.wishlist-button :product="$product" variant="full" />
                        <x-shop.compare-button :product="$product" variant="full" />
                        <a href="https://wa.me/{{ site('whatsapp') }}?text={{ $waText }}" target="_blank" rel="noopener"
                           class="inline-flex items-center gap-2 rounded-full border border-line px-6 py-3 font-bold transition-colors hover:border-accent hover:text-accent">اسأل على واتساب</a>
                        <a href="tel:{{ site('phone_tel') }}"
                           class="inline-flex items-center gap-2 rounded-full border border-line px-6 py-3 font-bold transition-colors hover:border-brand hover:text-brand">اتصل</a>
                    </div>

                    @if (session('wishlist_success') || session('compare_success') || session('compare_error'))
                        <p class="mt-3 text-sm font-semibold {{ session('compare_error') ? 'text-amber-700' : 'text-emerald-600' }}">
                            {{ session('wishlist_success') ?: (session('compare_success') ?: session('compare_error')) }}
                        </p>
                    @endif
                </div>

                {{-- بيانات سريعة --}}
                @if ($product->model_number || $product->sku || $product->warranty_note)
                    <dl class="mt-6 space-y-1.5 text-sm">
                        @if ($product->model_number)
                            <div class="flex gap-2"><dt class="text-muted">رقم الموديل:</dt><dd class="font-medium">{{ $product->model_number }}</dd></div>
                        @endif
                        @if ($product->sku)
                            <div class="flex gap-2"><dt class="text-muted">الكود (SKU):</dt><dd class="font-medium">{{ $product->sku }}</dd></div>
                        @endif
                        @if ($product->warranty_note)
                            <div class="flex gap-2"><dt class="text-muted">الضمان:</dt><dd class="font-medium">{{ $product->warranty_note }}</dd></div>
                        @endif
                    </dl>
                @endif

                {{-- المواصفات --}}
                @if ($specs->isNotEmpty())
                    <div class="mt-8">
                        <h2 class="mb-3 text-lg font-bold">المواصفات</h2>
                        <dl class="divide-y divide-line overflow-hidden rounded-2xl border border-line">
                            @foreach ($specs as $spec)
                                <div class="flex items-center justify-between gap-4 px-4 py-2.5 odd:bg-surface/50">
                                    <dt class="text-muted">{{ $spec['label'] }}</dt>
                                    <dd class="font-semibold">{{ $spec['value'] }}</dd>
                                </div>
                            @endforeach
                        </dl>
                    </div>
                @endif
            </div>
        </div>

        {{-- ============ الوصف ============ --}}
        @if (filled($product->description))
            <div class="mt-12 max-w-3xl">
                <h2 class="mb-3 text-lg font-bold">عن المنتج</h2>
                <div class="prose leading-relaxed text-ink/90">{!! nl2br(e($product->description)) !!}</div>
            </div>
        @endif

        {{-- ============ تقرير فحص الجهاز المستعمل ============ --}}
        @if ($isUsed && $product->usedDetails)
            @php $used = $product->usedDetails; @endphp
            <div class="mt-12 rounded-2xl border border-line bg-surface p-6 md:p-8">
                <h2 class="text-xl font-bold">تقرير فحص الجهاز</h2>
                <p class="mt-1 text-sm text-muted">فحص فني قبل العرض — عشان تشتري وأنت مطمّن.</p>

                <div class="mt-5 grid gap-3 text-sm sm:grid-cols-2 lg:grid-cols-4">
                    @if ($used->condition_grade)
                        <div class="rounded-xl bg-white px-4 py-3"><span class="text-muted">درجة الحالة</span><p class="mt-0.5 text-base font-bold">{{ $used->condition_grade }}</p></div>
                    @endif
                    @if ($used->manufacture_year)
                        <div class="rounded-xl bg-white px-4 py-3"><span class="text-muted">سنة الصنع</span><p class="mt-0.5 text-base font-bold">{{ $used->manufacture_year }}</p></div>
                    @endif
                    @if ($used->inspected_by)
                        <div class="rounded-xl bg-white px-4 py-3"><span class="text-muted">الفحص بواسطة</span><p class="mt-0.5 text-base font-bold">{{ $used->inspected_by }}</p></div>
                    @endif
                    @if ($used->inspected_at)
                        <div class="rounded-xl bg-white px-4 py-3"><span class="text-muted">تاريخ الفحص</span><p class="mt-0.5 text-base font-bold">{{ $used->inspected_at->format('Y/m/d') }}</p></div>
                    @endif
                </div>

                @if (! empty($used->inspection))
                    <h3 class="mt-6 mb-2 font-bold">قايمة الفحص</h3>
                    <ul class="space-y-2">
                        @foreach ($used->inspection as $row)
                            @continue(blank($row['item'] ?? null))
                            <li class="flex items-start gap-3 rounded-xl bg-white px-4 py-2.5">
                                <span class="mt-0.5 shrink-0 font-bold {{ ($row['ok'] ?? false) ? 'text-emerald-600' : 'text-muted' }}">{{ ($row['ok'] ?? false) ? '✓' : '—' }}</span>
                                <span class="flex-1">
                                    <span class="font-semibold">{{ $row['item'] }}</span>
                                    @if (filled($row['note'] ?? null))<span class="text-muted"> — {{ $row['note'] }}</span>@endif
                                </span>
                            </li>
                        @endforeach
                    </ul>
                @endif

                @if (filled($used->work_done))
                    <div class="mt-6">
                        <h3 class="mb-1 font-bold">الأعمال اللي اتعملت</h3>
                        <p class="leading-relaxed text-ink/90">{{ $used->work_done }}</p>
                    </div>
                @endif

                <p class="mt-6 text-sm text-muted">لتفاصيل درجات الحالة والضمان، شوف <a href="{{ route('shop.used') }}" class="font-semibold text-brand hover:underline">دليل التكييفات المستعملة</a>.</p>
            </div>
        @endif

        {{-- ============ التوصيل / مناطق التغطية ============ --}}
        <div class="mt-12 rounded-2xl border border-line bg-white p-6 md:p-8">
            <div class="flex items-start gap-4">
                <span class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl bg-brand-50 text-brand" aria-hidden="true">
                    <svg viewBox="0 0 24 24" class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M1 3h15v13H1zM16 8h4l3 3v5h-7M5.5 21a2.5 2.5 0 1 0 0-5 2.5 2.5 0 0 0 0 5ZM18.5 21a2.5 2.5 0 1 0 0-5 2.5 2.5 0 0 0 0 5Z"/></svg>
                </span>
                <div>
                    <h2 class="text-lg font-bold">التوصيل والتركيب</h2>
                    <p class="mt-1 leading-relaxed text-muted">بنوصّل ونركّب في {{ site('areas_label') }} والمناطق المحيطة. اتفق معانا على الميعاد والتفاصيل عند الطلب.</p>
                </div>
            </div>
            @if ($areas->isNotEmpty())
                <ul class="mt-4 flex flex-wrap gap-2">
                    @foreach ($areas as $area)
                        <li><a href="{{ url('/areas/' . $area->slug) }}" class="inline-block rounded-full bg-surface px-3.5 py-1.5 text-sm font-medium transition-colors hover:text-brand">{{ $area->name_ar }}</a></li>
                    @endforeach
                </ul>
            @endif
        </div>

        {{-- ============ تقييمات المنتج (المرحلة 6D) ============ --}}
        <div id="reviews" class="mt-14 scroll-mt-24">
            <h2 class="mb-5 text-2xl font-extrabold">تقييمات العملاء</h2>

            @if (session('review_success'))
                <div class="mb-5 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-semibold text-emerald-700">{{ session('review_success') }}</div>
            @endif

            @if ($reviews->isNotEmpty())
                <div class="mb-6 flex items-center gap-4 rounded-2xl border border-line bg-surface px-5 py-4">
                    <div class="text-center">
                        <div class="text-3xl font-extrabold text-ink">{{ number_format($product->ratingAverage(), 1) }}</div>
                        <x-shop.stars :value="$product->ratingAverage()" />
                    </div>
                    <p class="text-sm text-muted">من {{ $reviews->count() }} تقييم لعملاء اشتروا المنتج.</p>
                </div>

                <ul class="space-y-4">
                    @foreach ($reviews as $review)
                        <li class="rounded-2xl border border-line bg-white p-5">
                            <div class="flex items-center justify-between gap-3">
                                <div class="flex items-center gap-2">
                                    <span class="font-bold">{{ $review->authorName() }}</span>
                                    @if ($review->isVerified())
                                        <span class="inline-flex items-center gap-1 rounded-full bg-emerald-50 px-2 py-0.5 text-xs font-semibold text-emerald-700">
                                            <svg viewBox="0 0 24 24" class="h-3.5 w-3.5" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M20 6 9 17l-5-5"/></svg>
                                            شراء موثّق
                                        </span>
                                    @endif
                                </div>
                                <x-shop.stars :value="$review->rating" />
                            </div>
                            @if ($review->title)
                                <p class="mt-2 font-semibold">{{ $review->title }}</p>
                            @endif
                            <p class="mt-1 leading-relaxed text-ink/90">{{ $review->body }}</p>
                            @if ($review->created_at)
                                <p class="mt-2 text-xs text-muted">{{ $review->created_at->translatedFormat('j F Y') }}</p>
                            @endif
                        </li>
                    @endforeach
                </ul>
            @else
                <p class="text-muted">لسه مفيش تقييمات للمنتج ده. لو اشتريته واستلمته، شاركنا رأيك.</p>
            @endif

            {{-- فورم التقييم: للمؤهّلين بس (اشترى واتسلّم ومقيّمش قبل كده) --}}
            @auth('customer')
                @if ($canReview)
                    <div class="mt-8 rounded-2xl border border-line bg-surface p-5 md:p-6">
                        <h3 class="mb-4 text-lg font-bold">قيّم المنتج</h3>
                        @if ($errors->any())
                            <div class="mb-4 rounded-xl border border-red-200 bg-red-50 px-4 py-2.5 text-sm font-semibold text-red-700">
                                {{ $errors->first() }}
                            </div>
                        @endif
                        <form method="POST" action="{{ route('product.review.store', $product) }}" class="space-y-4" x-rating>
                            @csrf
                            <fieldset>
                                <legend class="mb-2 text-sm font-semibold">تقييمك</legend>
                                <div class="flex flex-row-reverse items-center justify-end gap-1" role="radiogroup" aria-label="التقييم بالنجوم">
                                    @for ($i = 5; $i >= 1; $i--)
                                        <label class="cursor-pointer">
                                            <input type="radio" name="rating" value="{{ $i }}" class="peer sr-only" @checked(old('rating') == $i) required>
                                            <svg viewBox="0 0 24 24" class="h-8 w-8 text-line transition-colors peer-checked:text-amber-400 hover:text-amber-300" fill="currentColor" aria-hidden="true"><path d="M12 2l2.9 6.3 6.6.6-5 4.4 1.5 6.5L12 17.8 6 20.3l1.5-6.5-5-4.4 6.6-.6L12 2z"/></svg>
                                            <span class="sr-only">{{ $i }} نجوم</span>
                                        </label>
                                    @endfor
                                </div>
                            </fieldset>
                            <div>
                                <label for="review-title" class="mb-1 block text-sm font-semibold">عنوان (اختياري)</label>
                                <input id="review-title" type="text" name="title" value="{{ old('title') }}" maxlength="120"
                                       class="w-full rounded-xl border border-line px-4 py-2.5 focus:border-brand focus:outline-none focus:ring-2 focus:ring-brand/30">
                            </div>
                            <div>
                                <label for="review-body" class="mb-1 block text-sm font-semibold">رأيك في المنتج</label>
                                <textarea id="review-body" name="body" rows="4" required minlength="10" maxlength="2000"
                                          class="w-full rounded-xl border border-line px-4 py-2.5 focus:border-brand focus:outline-none focus:ring-2 focus:ring-brand/30">{{ old('body') }}</textarea>
                            </div>
                            <p class="text-xs text-muted">تقييمك هيظهر بعد المراجعة.</p>
                            <button type="submit" class="inline-flex items-center rounded-full bg-brand px-7 py-3 font-bold text-white transition-colors hover:bg-brand-600">أرسل التقييم</button>
                        </form>
                    </div>
                @elseif ($existingReview)
                    <p class="mt-6 rounded-xl border border-line bg-surface px-4 py-3 text-sm text-muted">
                        قيّمت المنتج ده قبل كده.
                        @if ($existingReview->status === 'pending') تقييمك تحت المراجعة. @endif
                    </p>
                @endif
            @else
                <p class="mt-6 text-sm text-muted">
                    عايز تقيّم؟ <a href="{{ route('login') }}" class="font-semibold text-brand hover:underline">سجّل دخول</a> — التقييم متاح للعملاء اللي اشتروا المنتج واستلموه.
                </p>
            @endauth
        </div>

        {{-- ============ منتجات مشابهة ============ --}}
        @if ($related->isNotEmpty())
            <div class="mt-14">
                <h2 class="mb-5 text-2xl font-extrabold">منتجات مشابهة</h2>
                <div class="grid grid-cols-2 gap-4 sm:gap-5 lg:grid-cols-4">
                    @foreach ($related as $item)
                        <x-product-card :product="$item" />
                    @endforeach
                </div>
            </div>
        @endif
    </section>

    @if ($images->count() > 1)
        <script>
            document.querySelectorAll('[x-gallery]').forEach((gallery) => {
                const main = gallery.querySelector('#gallery-main');
                gallery.querySelectorAll('.gallery-thumb').forEach((btn) => {
                    btn.addEventListener('click', () => {
                        main.src = btn.dataset.src;
                        main.alt = btn.dataset.alt;
                        gallery.querySelectorAll('.gallery-thumb').forEach((b) => {
                            b.classList.toggle('border-brand', b === btn);
                            b.classList.toggle('border-line', b !== btn);
                        });
                    });
                });
            });
        </script>
    @endif
@endsection
