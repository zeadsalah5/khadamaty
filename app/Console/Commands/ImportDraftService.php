<?php

namespace App\Console\Commands;

use App\Models\Service;
use App\Support\AgencyScrub;
use App\Support\ContentCache;
use Illuminate\Console\Command;

/**
 * يضيف خدمة جديدة من database/seeders/data/services.php كـ «مسودة» لو مش موجودة.
 *
 * آمن على الإنتاج: مابيعدّلش ولا يمسح أي خدمة موجودة (عكس db:seed اللي بيكتب فوق
 * تعديلات الداشبورد)، والخدمة الجديدة غير منشورة => 404 للزائر وبره السايت ماب
 * لحد ما الفني يراجعها وتتنشر من /admin.
 */
class ImportDraftService extends Command
{
    protected $signature = 'khadamaty:import-draft-service {slug : slug الخدمة في ملف بيانات الخدمات}';

    protected $description = 'إضافة خدمة جديدة من بيانات الـ seed كمسودة (من غير تعديل أي خدمة موجودة)';

    /** أيقونة ونوع الحجز للخدمات الجديدة. */
    private const META = [
        'ac-relocation' => ['icon' => 'move', 'booking_service' => 'فك ونقل'],
    ];

    public function handle(): int
    {
        $slug = (string) $this->argument('slug');
        $data = require database_path('seeders/data/services.php');

        if (! isset($data[$slug])) {
            $this->error("الخدمة «{$slug}» مش موجودة في ملف البيانات.");

            return self::FAILURE;
        }

        if (Service::withoutGlobalScopes()->where('slug', $slug)->exists()) {
            $this->info("الخدمة «{$slug}» موجودة بالفعل — مفيش أي تعديل.");

            return self::SUCCESS;
        }

        $fields = $data[$slug];
        foreach (['excerpt', 'body', 'seo_description'] as $key) {
            if (isset($fields[$key])) {
                $fields[$key] = AgencyScrub::text($fields[$key]);
            }
        }
        if (isset($fields['faqs'])) {
            $fields['faqs'] = AgencyScrub::faqs($fields['faqs']);
        }

        $service = new Service($fields + (self::META[$slug] ?? []));
        $service->slug = $slug;
        $service->forceFill([
            'is_published'   => false,
            'published_at'   => null,
            'content_status' => Service::STATUS_DRAFT,
        ])->save();

        ContentCache::flush();

        $this->info("اتضافت الخدمة «{$service->name_ar}» كمسودة — راجعها وانشرها من لوحة التحكم.");

        return self::SUCCESS;
    }
}
