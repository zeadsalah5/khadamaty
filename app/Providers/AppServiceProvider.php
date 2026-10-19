<?php

namespace App\Providers;

use App\Models\Appliance;
use App\Models\Area;
use App\Models\Brand;
use App\Models\Faq;
use App\Models\MenuItem;
use App\Models\Package;
use App\Models\Page;
use App\Models\Post;
use App\Models\PostCategory;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\Redirect;
use App\Models\Review;
use App\Models\Service;
use App\Models\Setting;
use App\Models\Vendor;
use App\Observers\ContentCacheObserver;
use App\Payments\CashOnDelivery;
use App\Payments\PaymentManager;
use App\Support\Seo;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // خدمة SEO: singleton واحد لكل ريكوست، بتتظبط من الـ Controllers.
        $this->app->singleton(Seo::class, fn () => new Seo());

        // سجلّ طرق الدفع (6C: الدفع عند الاستلام فقط؛ الأونلاين يتضاف في 6E).
        $this->app->singleton(PaymentManager::class, function () {
            return (new PaymentManager())->register(new CashOnDelivery(), default: true);
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // مسح كاش المحتوى/الإعدادات تلقائياً عند أي تعديل من الداشبورد.
        $observed = [
            Setting::class, Service::class, Area::class, Brand::class,
            Appliance::class, Product::class, ProductCategory::class, Vendor::class,
            Post::class, PostCategory::class, Review::class,
            Package::class, Faq::class, Page::class, MenuItem::class, Redirect::class,
        ];

        foreach ($observed as $model) {
            $model::observe(ContentCacheObserver::class);
        }
    }
}
