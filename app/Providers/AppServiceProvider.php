<?php

namespace App\Providers;

use App\Models\Appliance;
use App\Models\Area;
use App\Models\Brand;
use App\Models\AreaServicePage;
use App\Models\BrandServicePage;
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
use App\Payments\PaymobGateway;
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

        // سجلّ طرق الدفع — COD دايماً (افتراضي). الدفع الأونلاين (Paymob) بيتسجّل
        // بس لو مفاتيحه متظبطة في .env، فالموقع بيشتغل عادي من غير مفاتيح.
        $this->app->singleton(PaymentManager::class, function () {
            $manager = (new PaymentManager())->register(new CashOnDelivery(), default: true);

            if (config('services.paymob.secret_key') && config('services.paymob.hmac_secret')) {
                $manager->register(new PaymobGateway());
            }

            return $manager;
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // مسح كاش المحتوى/الإعدادات تلقائياً عند أي تعديل من الداشبورد.
        $observed = [
            Setting::class, Service::class, Area::class, Brand::class, BrandServicePage::class, AreaServicePage::class,
            Appliance::class, Product::class, ProductCategory::class, Vendor::class,
            Post::class, PostCategory::class, Review::class,
            Package::class, Faq::class, Page::class, MenuItem::class, Redirect::class,
        ];

        foreach ($observed as $model) {
            $model::observe(ContentCacheObserver::class);
        }
    }
}
