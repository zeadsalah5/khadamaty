<?php

namespace App\Support;

use Illuminate\Support\Str;

/**
 * خدمة SEO مركزية.
 *
 * singleton واحد لكل ريكوست. الـ Controllers بتضبط القيم:
 *   app(Seo::class)->title('...')->description('...')->robots('index,follow');
 * والـ layout بيقرأ منها عن طريق partials/meta.
 *
 * كل القيم ليها default محسوب من config/site.php.
 */
class Seo
{
    protected ?string $title = null;
    protected ?string $description = null;
    protected ?string $canonicalPath = null; // مسار نسبي أو URL كامل؛ null => الرابط الحالي
    protected string $robots = 'index,follow';
    protected string $ogType = 'website';
    protected ?string $ogImage = null;

    public function title(?string $value): static
    {
        $this->title = $value ? trim($value) : null;

        return $this;
    }

    public function description(?string $value): static
    {
        $this->description = $value ? trim($value) : null;

        return $this;
    }

    /** مسار نسبي ('/services') أو URL كامل؛ بيتحوّل لـ canonical مطلق. */
    public function canonical(?string $pathOrUrl): static
    {
        $this->canonicalPath = $pathOrUrl;

        return $this;
    }

    public function robots(string $value): static
    {
        $this->robots = $value;

        return $this;
    }

    /** اختصار للصفحات الفاضية/"قريباً". */
    public function noindex(): static
    {
        $this->robots = 'noindex,follow';

        return $this;
    }

    public function ogType(string $value): static
    {
        $this->ogType = $value;

        return $this;
    }

    public function ogImage(?string $pathOrUrl): static
    {
        $this->ogImage = $pathOrUrl;

        return $this;
    }

    /* ----------------------------------------------------------------
     | قراءة القيم النهائية (بتستخدمها partials/meta)
     |----------------------------------------------------------------*/

    public function getPageTitle(): string
    {
        $company = config('site.name');
        $part = $this->title
            ?: 'صيانة وتركيب تكييفات وأجهزة منزلية في ' . config('site.areas_label');

        // الصيغة المطلوبة: {عنوان الصفحة} | خدماتي
        return $part . ' | ' . $company;
    }

    public function getDescription(): string
    {
        return $this->description
            ?: config('site.name') . ' — ماركت بليس وخدمات صيانة للأجهزة والتكييفات في '
               . config('site.areas_label') . '. احجز فني متخصص والدفع بعد الشغل.';
    }

    /**
     * الأساس المطلق للروابط: https://{canonical_host} لو متحدد، وإلا APP_URL.
     * (ده بيضمن إن الـ canonical مابيتأثرش بالـ host اللي جاي في الريكوست.)
     */
    public function baseUrl(): string
    {
        $host = trim((string) config('site.canonical_host'));

        if ($host !== '') {
            return 'https://' . ltrim($host, '/');
        }

        return rtrim((string) config('app.url'), '/');
    }

    public function getCanonical(): string
    {
        $base = $this->baseUrl();
        $value = $this->canonicalPath;

        // null => المسار الحالي من الريكوست (مع الكويري سترينج متشال)
        if ($value === null) {
            $path = request()->getPathInfo();

            return $this->join($base, $path);
        }

        // URL كامل يرجع زي ما هو
        if (Str::startsWith($value, ['http://', 'https://'])) {
            return $value;
        }

        return $this->join($base, $value);
    }

    public function getRobots(): string
    {
        return $this->robots;
    }

    public function getOgType(): string
    {
        return $this->ogType;
    }

    public function getOgImage(): string
    {
        $img = $this->ogImage ?: config('site.default_og_image');

        if (Str::startsWith($img, ['http://', 'https://'])) {
            return $img;
        }

        return $this->join($this->baseUrl(), '/' . ltrim($img, '/'));
    }

    protected function join(string $base, string $path): string
    {
        return rtrim($base, '/') . '/' . ltrim($path, '/');
    }
}
