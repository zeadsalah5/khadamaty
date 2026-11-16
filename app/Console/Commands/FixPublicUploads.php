<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

/**
 * ينقل الصور اللي اترفعت بالغلط في {base}/public/images للجذر العام الفعلي
 * (public_path('images')) لما الاتنين مختلفين — حالة الاستضافة المشتركة بعد
 * config:cache اتعمل بمسار عام غلط. مابيكتبش فوق ملف موجود ومابيمسحش غير اللي نقله.
 */
class FixPublicUploads extends Command
{
    protected $signature = 'khadamaty:fix-public-uploads';

    protected $description = 'نقل الصور المرفوعة في مسار عام غلط للمسار الصح';

    public function handle(): int
    {
        $wrong = base_path('public/images');
        $right = public_path('images');

        if (realpath($wrong) === realpath($right) || ! is_dir($wrong)) {
            $this->info('مفيش صور في مسار غلط.');

            return self::SUCCESS;
        }

        $moved = 0;
        foreach (File::allFiles($wrong) as $file) {
            $target = $right . DIRECTORY_SEPARATOR . $file->getRelativePathname();
            if (is_file($target)) {
                continue; // موجود في المكان الصح بالفعل
            }
            File::ensureDirectoryExists(dirname($target));
            if (File::copy($file->getPathname(), $target)) {
                File::delete($file->getPathname());
                $moved++;
                $this->line('اتنقل: images/' . str_replace('\\', '/', $file->getRelativePathname()));
            }
        }

        $this->info("اتنقل {$moved} ملف.");

        return self::SUCCESS;
    }
}
