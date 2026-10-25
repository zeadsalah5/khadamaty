<?php

namespace App\Http\Controllers\Content\Concerns;

use Illuminate\Database\Eloquent\Model;

/**
 * معاينة المسوّدات للأدمن/المحرّر.
 *
 * الصفحة غير المنشورة بتوصل للكونترولر بس لو can_preview_drafts (شوف Publishable
 * و BrandServiceController). لو كانت مسوّدة:
 *   - بنعلّم الريكوست عشان middleware يضيف X-Robots-Tag: noindex.
 *   - بنفرض robots=noindex على الـ meta.
 *   - بنرجّع true عشان الـ view يعرض شريط «مسودة».
 */
trait PreviewsDrafts
{
    /** يعلّم الصفحة كمسوّدة لو مش منشورة؛ بيرجّع $draft للـ view. */
    protected function previewDraft(Model $model): bool
    {
        if ($model->is_published) {
            return false;
        }

        // مسوّدة بتتشاف كمعاينة للأدمن فقط.
        $this->seo()->noindex();
        request()->attributes->set('draft_preview', true);

        return true;
    }
}
