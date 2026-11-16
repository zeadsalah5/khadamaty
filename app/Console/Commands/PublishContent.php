<?php

namespace App\Console\Commands;

use App\Models\Area;
use App\Models\AreaServicePage;
use App\Models\Brand;
use App\Models\BrandServicePage;
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
        {type : brand | brand-service | area-service}
        {key : slug الماركة، أو brand|service، أو area|service}
        {--replace : استبدال محتوى سجل منشور بالفعل}
        {--ack-similarity : النشر حتى لو التشابه فوق 30%}';

    protected $description = 'إضافة/نشر صفحة محتوى واحدة من بيانات الـ seed';

    public function handle(ContentSimilarity $similarity): int
    {
        $type = (string) $this->argument('type');
        // «:» مقبولة بدل «|» عشان الأمر يتكتب في cron من غير رموز shell.
        $key = str_replace(':', '|', (string) $this->argument('key'));

        $record = match ($type) {
            'brand'         => $this->brand($key),
            'brand-service' => $this->brandService($key),
            'area-service'  => $this->areaService($key),
            default         => null,
        };

        if (! $record) {
            $this->error("مش لاقي بيانات «{$type}: {$key}» (أو الماركة/المنطقة/الخدمة الأساسية مش موجودة).");

            return self::FAILURE;
        }

        if ($record->exists && $record->is_published && ! $this->option('replace')) {
            $this->info('الصفحة منشورة بالفعل — مفيش أي تعديل.');

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
