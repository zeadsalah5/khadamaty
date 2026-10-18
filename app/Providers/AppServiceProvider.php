<?php

namespace App\Providers;

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
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
