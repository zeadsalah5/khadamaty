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
}
