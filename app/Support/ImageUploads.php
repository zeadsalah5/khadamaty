<?php

namespace App\Support;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\ImageManager;

/**
 * تحويل الصور المرفوعة من الداشبورد لـ WebP وتصغيرها قبل التخزين (المرحلة C2).
 * بتتستخدم مع FileUpload->saveUploadedFileUsing(). بترجّع اسم الملف المخزّن.
 */
class ImageUploads
{
    /**
     * يخزّن الملف المرفوع كـ WebP على القرص المطلوب ويرجّع اسمه.
     * لو التحويل فشل لأي سبب، بيخزّن الأصل زي ما هو (مايكسرش الرفع).
     */
    public static function storeWebp(mixed $file, string $disk, int $maxWidth = 1600, int $quality = 82): string
    {
        try {
            $manager = new ImageManager(new Driver());
            $image = $manager->read($file->getRealPath());

            if ($image->width() > $maxWidth) {
                $image->scaleDown(width: $maxWidth);
            }

            $binary = (string) $image->toWebp($quality);
            $name = Str::random(24) . '.webp';

            Storage::disk($disk)->put($name, $binary);

            return $name;
        } catch (\Throwable $e) {
            // fallback: خزّن الأصل، بس باقصر الامتداد على صور معروفة (دفاع في العمق — M1).
            $ext = strtolower((string) $file->getClientOriginalExtension());
            $ext = in_array($ext, ['jpg', 'jpeg', 'png', 'webp'], true) ? $ext : 'jpg';
            $name = Str::random(24) . '.' . $ext;
            Storage::disk($disk)->putFileAs('', $file, $name);

            return $name;
        }
    }

    /**
     * خلفية الهيرو full-bleed: بتولّد مقاسين WebP (1920 + 960) باسم أساس واحد
     * ({base}.webp و{base}-960.webp) عشان srcset/sizes، وبترجّع الاسم الأساس (1920).
     * حد العرض هنا 1920 (أعلى من الافتراضي) لأنها صورة بعرض الشاشة.
     * لو التحويل فشل بيرجع لـ storeWebp العادي (مايكسرش الرفع).
     */
    public static function storeHeroResponsive(mixed $file, string $disk, int $quality = 80): string
    {
        try {
            $manager = new ImageManager(new Driver());
            $base = Str::random(24);

            // المقاس الكبير (1920)
            $large = $manager->read($file->getRealPath());
            if ($large->width() > 1920) {
                $large->scaleDown(width: 1920);
            }
            Storage::disk($disk)->put($base . '.webp', (string) $large->toWebp($quality));

            // المقاس الصغير (960) — من الأصل تاني عشان أوضح تصغير
            $small = $manager->read($file->getRealPath());
            if ($small->width() > 960) {
                $small->scaleDown(width: 960);
            }
            Storage::disk($disk)->put($base . '-960.webp', (string) $small->toWebp($quality));

            return $base . '.webp';
        } catch (\Throwable $e) {
            // fallback: مقاس واحد عبر المسار العادي.
            return static::storeWebp($file, $disk, 1920, $quality);
        }
    }
}
