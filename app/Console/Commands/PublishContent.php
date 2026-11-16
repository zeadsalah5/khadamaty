<?php

namespace App\Console\Commands;

use App\Models\Area;
use App\Models\AreaServicePage;
use App\Models\Brand;
use App\Models\BrandServicePage;
use App\Models\Redirect;
use App\Models\Service;
use App\Models\User;
use App\Support\AgencyScrub;
use App\Support\ContentCache;
use App\Support\ContentSimilarity;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Model;

/**
 * ينشر صفحة محتوى جديدة من بيانات الـ seed على الموقع (ماركة / ماركة×خدمة / منطقة×خدمة).
 *
 * آمن على الإنتاج: بيشتغل على سجل واحد بس. لو السجل موجود ومنشور بالفعل مابيلمسوش
 * (تعديلات الداشبورد بتفضل) إلا مع --replace. بيفشل ومابينشرش لو المحتوى فيه TODO
 * أو ناقص، أو لو التشابه مع صفحة منشورة من نفس النوع فوق 30% (إلا مع --ack-similarity).
 * مابيألّفش أسعار: حقول السعر/المدة اللي فيها TODO بتتفضّى.
 */
class PublishContent extends Command
{
    protected $signature = 'khadamaty:publish-content
        {type : brand | area | brand-service | area-service}
        {key : slug الماركة، أو brand|service، أو area|service}
        {--replace : استبدال محتوى سجل منشور بالفعل}
        {--draft : حفظ الصفحة «جاهزة للنشر» من غير نشرها (صاحب الموقع ينشرها من الداشبورد)}
        {--ack-similarity : النشر حتى لو التشابه فوق 30%}';

    protected $description = 'إضافة/نشر صفحة محتوى واحدة من بيانات الـ seed';

    public function handle(ContentSimilarity $similarity): int
    {
        $type = (string) $this->argument('type');
        // أكتر من مفتاح مفصولين بفاصلة => كل واحد بيتنشر لوحده (دفعة في أمر واحد).
        $keys = array_filter(array_map('trim', explode(',', (string) $this->argument('key'))));
        $failed = 0;

        foreach ($keys as $key) {
            // «:» مقبولة بدل «|» عشان الأمر يتكتب في cron من غير رموز shell.
            if ($this->publishOne($type, str_replace(':', '|', $key), $similarity) !== self::SUCCESS) {
                $failed++;
            }
        }

        return $failed === 0 && $keys !== [] ? self::SUCCESS : self::FAILURE;
    }

    private function publishOne(string $type, string $key, ContentSimilarity $similarity): int
    {

        $record = match ($type) {
            'brand'         => $this->brand($key),
            'area'          => $this->area($key),
            'brand-service' => $this->brandService($key),
            'area-service'  => $this->areaService($key),
            default         => null,
        };

        if (! $record) {
            $this->error("مش لاقي بيانات «{$type}: {$key}» (أو الماركة/المنطقة/الخدمة الأساسية مش موجودة).");

            return self::FAILURE;
        }

        if ($record->exists && $record->is_published && ! $this->option('replace')) {
            $this->info("«{$type}: {$key}» منشورة بالفعل — مفيش أي تعديل.");

            return self::SUCCESS;
        }

        foreach (['price_note', 'duration_note'] as $field) {
            if (filled($record->{$field} ?? null) && public_note($record->{$field}) === null) {
                $record->{$field} = null;
            }
        }

        $adminId = User::where('role', 'admin')->orderBy('id')->value('id');
        $record->content_status = $record::STATUS_READY;
        $record->reviewed_by ??= $adminId;
        $record->reviewed_at ??= now();

        if (! $record->canPublish()) {
            $this->error('ماينفعش تتنشر: ' . implode('، ', $record->publishBlockers()));

            return self::FAILURE;
        }

        $max = $similarity->maxAgainstPublished($record);
        $pct = (int) $max['pct'];
        if ($pct > 30) {
            if (! $this->option('ack-similarity')) {
                $this->error("التشابه مع صفحة منشورة {$pct}% (> 30%) — عدّل المحتوى أو استخدم --ack-similarity.");

                return self::FAILURE;
            }
            $record->similarity_ack_by = $adminId;
            $record->similarity_ack_at = now();
        }
        $record->similarity_pct = $pct;

        if ($this->option('draft')) {
            // جاهزة ومتراجعة لكن غير منشورة: 404 للزائر لحد ما تتنشر من الداشبورد.
            if (! $record->is_published) {
                $record->forceFill(['is_published' => false, 'published_at' => null])->save();
                ContentCache::flush();
            }
            $this->info("اتحفظت «{$type}: {$key}» جاهزة للنشر من غير نشر (تشابه {$pct}%).");

            return self::SUCCESS;
        }

        $record->forceFill([
            'is_published' => true,
            'published_at' => $record->published_at ?? now(),
        ])->save();

        ContentCache::flush();

        $this->info("اتنشرت «{$type}: {$key}» (تشابه {$pct}%).");

        return self::SUCCESS;
    }

    private function brand(string $slug): ?Model
    {
        $data = (require database_path('seeders/data/brands.php'))[$slug] ?? null;
        if (! $data) {
            return null;
        }

        $brand = Brand::withoutGlobalScopes()->where('slug', $slug)->first()
            ?? tap(new Brand, fn (Brand $b) => $b->slug = $slug);

        return $this->fillFrom($brand, $data);
    }

    private function area(string $slug): ?Model
    {
        $data = (require database_path('seeders/data/areas.php'))[$slug] ?? null;
        if (! $data) {
            return null;
        }

        $area = Area::withoutGlobalScopes()->where('slug', $slug)->first()
            ?? tap(new Area, fn (Area $a) => $a->slug = $slug);

        // منطقة كانت متشالة قبل كده ومتحوّلة (301) لهب المناطق => التحويل القديم بيتشال.
        Redirect::query()->whereIn('from', ["areas/{$slug}", "/areas/{$slug}"])->delete();

        if (filled($data['eta_note'] ?? null) && public_note($data['eta_note']) === null) {
            $data['eta_note'] = null;
        }

        return $this->fillFrom($area, $data);
    }

    private function brandService(string $key): ?Model
    {
        [$brandSlug, $serviceSlug] = array_pad(explode('|', $key, 2), 2, '');
        $data = (require database_path('seeders/data/brand_services.php'))[$key] ?? null;
        $brandId = Brand::withoutGlobalScopes()->where('slug', $brandSlug)->value('id');
        $serviceId = Service::withoutGlobalScopes()->where('slug', $serviceSlug)->value('id');
        if (! $data || ! $brandId || ! $serviceId) {
            return null;
        }

        $page = BrandServicePage::withoutGlobalScopes()
            ->firstOrNew(['brand_id' => $brandId, 'service_id' => $serviceId]);

        return $this->fillFrom($page, $data);
    }

    private function areaService(string $key): ?Model
    {
        [$areaSlug, $serviceSlug] = array_pad(explode('|', $key, 2), 2, '');
        $data = (require database_path('seeders/data/area_services.php'))[$key] ?? null;
        $areaId = Area::withoutGlobalScopes()->where('slug', $areaSlug)->value('id');
        $serviceId = Service::withoutGlobalScopes()->where('slug', $serviceSlug)->value('id');
        if (! $data || ! $areaId || ! $serviceId) {
            return null;
        }

        $page = AreaServicePage::withoutGlobalScopes()
            ->firstOrNew(['area_id' => $areaId, 'service_id' => $serviceId]);

        return $this->fillFrom($page, $data);
    }

    /** بيملا المحتوى من البيانات لسجل جديد أو غير منشور (أو مع --replace). */
    private function fillFrom(Model $record, array $data): Model
    {
        if ($record->exists && $record->is_published && ! $this->option('replace')) {
            return $record;
        }

        foreach (['excerpt', 'intro', 'body', 'seo_description'] as $k) {
            if (isset($data[$k])) {
                $data[$k] = AgencyScrub::text($data[$k]);
            }
        }
        if (isset($data['faqs'])) {
            $data['faqs'] = AgencyScrub::faqs($data['faqs']);
        }

        return $record->fill($data);
    }
}
