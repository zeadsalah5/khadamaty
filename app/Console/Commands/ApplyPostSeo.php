<?php

namespace App\Console\Commands;

use App\Models\Post;
use App\Models\Service;
use App\Support\ContentCache;
use Illuminate\Console\Command;

/**
 * بيطبّق عناوين/أوصاف SEO «تنفيذية» من post_seo_actions.php على مقالات المناطق.
 * المقال اللي صاحب الموقع عدّل عنوان الـ SEO بتاعه من الداشبورد (مختلف عن old_title)
 * بيتساب زي ما هو. --force يطبّق على الكل.
 */
class ApplyPostSeo extends Command
{
    protected $signature = 'khadamaty:post-seo {--force : تطبيق حتى على المقالات اللي اتعدّلت يدوياً} {--services : صفحات الخدمات بدل المقالات}';

    protected $description = 'تحديث عنوان ووصف SEO لمقالات المناطق (من غير لمس المحتوى)';

    public function handle(): int
    {
        $services = (bool) $this->option('services');
        $data = require database_path('seeders/data/' . ($services ? 'service_seo_actions.php' : 'post_seo_actions.php'));
        $model = $services ? Service::class : Post::class;
        $updated = $skipped = $missing = 0;

        foreach ($data as $slug => $row) {
            $post = $model::withoutGlobalScopes()->where('slug', $slug)->first();
            if (! $post) {
                $missing++;
                continue;
            }

            $current = trim((string) $post->seo_title);
            $editedByOwner = $current !== ''
                && $current !== trim((string) ($row['old_title'] ?? ''))
                && $current !== $row['seo_title'];

            if ($editedByOwner && ! $this->option('force')) {
                $this->line("اتساب (متعدّل يدوياً): {$slug} — {$current}");
                $skipped++;
                continue;
            }

            $post->forceFill([
                'seo_title' => $row['seo_title'],
                'seo_description' => $row['seo_description'],
            ])->saveQuietly();
            $updated++;
        }

        ContentCache::flush();
        $this->info("اتحدّث {$updated} · اتساب {$skipped} · مش موجود {$missing}");

        return self::SUCCESS;
    }
}
