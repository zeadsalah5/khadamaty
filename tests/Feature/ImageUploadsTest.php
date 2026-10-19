<?php

namespace Tests\Feature;

use App\Support\ImageUploads;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/**
 * M1 — fallback رفع الصور بيقصر الامتداد على صور معروفة لو فشل التحويل.
 */
class ImageUploadsTest extends TestCase
{
    public function test_fallback_clamps_unknown_extension_to_jpg(): void
    {
        Storage::fake('products');

        // ملف بامتداد غير صورة ومحتوى مش صورة => تحويل WebP بيفشل => fallback.
        $file = UploadedFile::fake()->createWithContent('payload.svg', '<svg>not-an-image</svg>');

        $name = ImageUploads::storeWebp($file, 'products');

        $this->assertStringEndsWith('.jpg', $name);
        $this->assertStringNotContainsString('.svg', $name);
        Storage::disk('products')->assertExists($name);
    }

    public function test_keeps_known_image_extension_on_fallback(): void
    {
        Storage::fake('products');

        // محتوى مش صورة صالحة لكن الامتداد معروف (png) => fallback بيحافظ عليه.
        $file = UploadedFile::fake()->createWithContent('photo.png', 'not-really-png-bytes');

        $name = ImageUploads::storeWebp($file, 'products');

        $this->assertStringEndsWith('.png', $name);
        Storage::disk('products')->assertExists($name);
    }
}
