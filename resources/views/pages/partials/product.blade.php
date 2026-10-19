{{--
    تفاصيل منتج المتجر (المرحلة 6A — عرض بسيط).
    $product: موديل Product محمّل بعلاقاته (category.attributes, brand, images, usedDetails).
--}}
@php
    use Illuminate\Support\Facades\Storage;

    $disk = Storage::disk('products');

    // الصور: الرئيسية الأول، وبعدين الباقي بالترتيب.
    $images = $product->images
        ->sortByDesc(fn ($img) => $img->is_primary ? 1 : 0)
        ->values();

    // المواصفات بترتيب حقول القسم مع الـ labels.
    $specs = collect($product->category?->attributes ?? [])
        ->map(function ($attr) use ($product) {
            $value = data_get($product->specs, $attr->key);
            if (blank($value)) {
                return null;
            }
            return [
                'label' => $attr->label_ar,
                'value' => $attr->unit ? ($value . ' ' . $attr->unit) : $value,
            ];
        })
        ->filter()
        ->values();

    $isUsed = $product->isUsed();
    $display = $product->displayPrice();
    $hasSale = $product->sale_price !== null && (float) $product->sale_price > 0
        && $product->hasRealPrice() && (float) $product->sale_price < (float) $product->price;
@endphp

<section class="mx-auto max-w-6xl px-4 pb-16 md:pb-20">
    <div class="grid gap-8 md:grid-cols-2 md:gap-12">

        {{-- معرض الصور --}}
        <div>
            @if ($images->isNotEmpty())
                <div class="overflow-hidden rounded-2xl border border-line bg-white">
                    <img src="{{ $disk->url($images->first()->path) }}"
                         alt="{{ $images->first()->alt ?: $product->name_ar }}"
                         class="aspect-square w-full object-contain">
                </div>
                @if ($images->count() > 1)
                    <div class="mt-3 grid grid-cols-5 gap-2">
                        @foreach ($images->take(5) as $img)
                            <div class="overflow-hidden rounded-lg border border-line bg-white">
                                <img src="{{ $disk->url($img->path) }}"
                                     alt="{{ $img->alt ?: $product->name_ar }}"
                                     class="aspect-square w-full object-contain">
                            </div>
                        @endforeach
                    </div>
                @endif
            @else
                <div class="flex aspect-square w-full items-center justify-center rounded-2xl border border-dashed border-line bg-surface text-muted">
                    لا توجد صورة
                </div>
            @endif
        </div>

        {{-- معلومات المنتج --}}
        <div>
            <div class="flex flex-wrap items-center gap-2">
                <span class="rounded-full px-3 py-1 text-sm font-bold
                    {{ $isUsed ? 'bg-amber-100 text-amber-800' : 'bg-sky-100 text-sky-800' }}">
                    {{ $isUsed ? 'مستعمل' : 'جديد' }}
                </span>
                @if ($product->category)
                    <span class="rounded-full bg-surface px-3 py-1 text-sm text-muted">{{ $product->category->name_ar }}</span>
                @endif
                @if ($product->brand)
                    <span class="rounded-full bg-surface px-3 py-1 text-sm text-muted">{{ $product->brand->name_ar }}</span>
                @endif
            </div>

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

                <p class="mt-2 text-sm font-semibold {{ $product->stock_qty > 0 ? 'text-emerald-600' : 'text-muted' }}">
                    {{ $product->stock_qty > 0 ? 'متوفر' : 'غير متوفر حالياً' }}
                </p>
            </div>

            {{-- بيانات سريعة --}}
            @if ($product->model_number || $product->warranty_note)
                <dl class="mt-5 space-y-1.5 text-sm">
                    @if ($product->model_number)
                        <div class="flex gap-2"><dt class="text-muted">رقم الموديل:</dt><dd class="font-medium">{{ $product->model_number }}</dd></div>
                    @endif
                    @if ($product->warranty_note)
                        <div class="flex gap-2"><dt class="text-muted">الضمان:</dt><dd class="font-medium">{{ $product->warranty_note }}</dd></div>
                    @endif
                </dl>
            @endif

            <a href="{{ url('/') }}#booking"
               class="mt-6 inline-block rounded-full bg-brand px-7 py-3 font-bold text-white transition-colors hover:bg-brand-600">
                اطلب المنتج أو استفسر
            </a>

            {{-- المواصفات --}}
            @if ($specs->isNotEmpty())
                <div class="mt-8">
                    <h2 class="mb-3 text-lg font-bold">المواصفات</h2>
                    <dl class="divide-y divide-line rounded-2xl border border-line">
                        @foreach ($specs as $spec)
                            <div class="flex items-center justify-between gap-4 px-4 py-2.5">
                                <dt class="text-muted">{{ $spec['label'] }}</dt>
                                <dd class="font-semibold">{{ $spec['value'] }}</dd>
                            </div>
                        @endforeach
                    </dl>
                </div>
            @endif
        </div>
    </div>

    {{-- الوصف --}}
    @if (filled($product->description))
        <div class="mt-12 max-w-3xl">
            <h2 class="mb-3 text-lg font-bold">عن المنتج</h2>
            <div class="prose leading-relaxed text-ink/90">{!! nl2br(e($product->description)) !!}</div>
        </div>
    @endif

    {{-- تقرير فحص الجهاز المستعمل (عنصر الثقة الأساسي للمستعمل) --}}
    @if ($isUsed && $product->usedDetails)
        @php $used = $product->usedDetails; @endphp
        <div class="mt-12 rounded-2xl border border-line bg-surface p-6 md:p-8">
            <h2 class="text-xl font-bold">تقرير فحص الجهاز</h2>
            <p class="mt-1 text-sm text-muted">فحص فني قبل العرض — عشان تشتري وأنت مطمّن.</p>

            <div class="mt-5 grid gap-3 sm:grid-cols-2 lg:grid-cols-4 text-sm">
                @if ($used->condition_grade)
                    <div><span class="text-muted">درجة الحالة:</span> <span class="font-bold">{{ $used->condition_grade }}</span></div>
                @endif
                @if ($used->manufacture_year)
                    <div><span class="text-muted">سنة الصنع:</span> <span class="font-bold">{{ $used->manufacture_year }}</span></div>
                @endif
                @if ($used->inspected_by)
                    <div><span class="text-muted">الفحص بواسطة:</span> <span class="font-bold">{{ $used->inspected_by }}</span></div>
                @endif
                @if ($used->inspected_at)
                    <div><span class="text-muted">تاريخ الفحص:</span> <span class="font-bold">{{ $used->inspected_at->format('Y/m/d') }}</span></div>
                @endif
            </div>

            @if (! empty($used->inspection))
                <ul class="mt-6 space-y-2">
                    @foreach ($used->inspection as $row)
                        @continue(blank($row['item'] ?? null))
                        <li class="flex items-start gap-3 rounded-xl bg-white px-4 py-2.5">
                            <span class="mt-0.5 shrink-0 font-bold {{ ($row['ok'] ?? false) ? 'text-emerald-600' : 'text-muted' }}">
                                {{ ($row['ok'] ?? false) ? '✓' : '—' }}
                            </span>
                            <span class="flex-1">
                                <span class="font-semibold">{{ $row['item'] }}</span>
                                @if (filled($row['note'] ?? null))
                                    <span class="text-muted"> — {{ $row['note'] }}</span>
                                @endif
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
        </div>
    @endif
</section>
