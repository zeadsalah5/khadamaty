<?php

namespace Tests\Feature;

use App\Filament\Resources\ProjectResource;
use App\Models\Project;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * معرض الأعمال: صفحة /portfolio، العمل المفرد، نشر/إخفاء، والداشبورد.
 */
class PortfolioTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    private function makeProject(array $attrs = []): Project
    {
        return Project::create(array_merge([
            'slug'         => 'central-ac-tagamoa',
            'title'        => 'تركيب تكييف مركزي — فيلا بالتجمع',
            'service_type' => 'تركيب',
            'area_name'    => 'التجمع الخامس',
            'summary'      => 'تركيب وحدة مركزية وتأسيس مواسير كامل.',
            'is_published' => true,
            'position'     => 0,
        ], $attrs));
    }

    public function test_portfolio_index_renders_published_projects(): void
    {
        $this->makeProject();

        $this->get('/portfolio')
            ->assertOk()
            ->assertSee('معرض أعمالنا')
            ->assertSee('تركيب تكييف مركزي — فيلا بالتجمع');
    }

    public function test_portfolio_shows_experience_years(): void
    {
        $this->get('/portfolio')->assertOk()->assertSee('15');
    }

    public function test_project_detail_renders(): void
    {
        $this->makeProject();

        $this->get('/portfolio/central-ac-tagamoa')
            ->assertOk()
            ->assertSee('تركيب تكييف مركزي — فيلا بالتجمع');
    }

    public function test_unpublished_project_is_404(): void
    {
        $this->makeProject(['slug' => 'hidden-work', 'is_published' => false]);

        $this->get('/portfolio/hidden-work')->assertNotFound();
    }

    public function test_portfolio_link_in_header_nav_when_published(): void
    {
        $this->makeProject();

        $this->get('/')->assertOk()->assertSee('أعمالنا');
    }

    public function test_no_portfolio_link_when_empty(): void
    {
        // مفيش عمل منشور → الرابط مايظهرش (زي المتجر الفاضي).
        $this->get('/')->assertOk()->assertDontSee('أعمالنا');
    }

    public function test_admin_can_access_projects_resource(): void
    {
        $admin = User::where('role', 'admin')->firstOrFail();

        $this->actingAs($admin)
            ->get(ProjectResource::getUrl('index'))
            ->assertOk();

        $this->actingAs($admin)
            ->get(ProjectResource::getUrl('create'))
            ->assertOk();
    }
}
