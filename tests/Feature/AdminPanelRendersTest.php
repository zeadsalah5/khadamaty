<?php

namespace Tests\Feature;

use App\Filament\Resources\AreaResource;
use App\Filament\Resources\AreaServicePageResource;
use App\Filament\Resources\BrandResource;
use App\Filament\Resources\BrandServicePageResource;
use App\Filament\Resources\PostResource;
use App\Filament\Resources\ServiceResource;
use App\Models\Area;
use App\Models\User;
use App\Support\Settings;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

/**
 * تأكيد إن لوحة الإدارة بتترندر من غير أخطاء (الفورم والـ widget بيتبنوا وقت الرندر).
 * بيمسك أخطاء زي «Cannot access trait constant directly» اللي مابتظهرش في اختبارات
 * الواجهة العامة لأن فورم Filament بيتبني بس في المتصفح.
 */
class AdminPanelRendersTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Settings::flush();
        $this->seed();
        Cache::flush();
    }

    protected function admin(): User
    {
        return User::where('role', 'admin')->firstOrFail();
    }

    public function test_dashboard_renders_with_review_widget(): void
    {
        $this->actingAs($this->admin())->get('/admin')->assertOk();
    }

    public function test_create_forms_render_with_review_fields(): void
    {
        $this->actingAs($this->admin());

        foreach ([ServiceResource::class, BrandResource::class, AreaResource::class,
            PostResource::class, BrandServicePageResource::class, AreaServicePageResource::class] as $resource) {
            $this->get($resource::getUrl('create'))->assertOk();
        }
    }

    public function test_area_edit_form_renders(): void
    {
        $area = Area::where('slug', '6th-october')->firstOrFail();

        $this->actingAs($this->admin())
            ->get(AreaResource::getUrl('edit', ['record' => $area]))
            ->assertOk();
    }
}
