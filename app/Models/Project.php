<?php

namespace App\Models;

use App\Models\Concerns\Publishable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

/**
 * عمل في معرض الأعمال (Portfolio). بيتدار من الداشبورد.
 * غير المنشور = 404 للزائر (Publishable). الصور على قرص portfolio (WebP).
 */
class Project extends Model
{
    use Publishable;

    protected $fillable = [
        'slug', 'title', 'service_type', 'area_name', 'summary', 'body',
        'cover', 'images', 'completed_at', 'is_featured', 'position', 'is_published',
    ];

    protected $casts = [
        'images'       => 'array',
        'completed_at' => 'date',
        'is_featured'  => 'boolean',
        'is_published' => 'boolean',
    ];

    /** رابط صورة الغلاف (أو أول صورة في المعرض) أو null. */
    public function coverUrl(): ?string
    {
        $img = $this->cover ?: ($this->images[0] ?? null);

        return $img ? Storage::disk('portfolio')->url($img) : null;
    }

    /** روابط صور المعرض (بترتيبها). */
    public function galleryUrls(): array
    {
        return collect($this->images ?? [])
            ->filter()
            ->map(fn ($f) => Storage::disk('portfolio')->url($f))
            ->values()
            ->all();
    }
}
