<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Support\Seo;
use Illuminate\View\View;

/**
 * معرض الأعمال (Portfolio): /portfolio (قائمة) و /portfolio/{slug} (عمل مفرد).
 * بيعرض الأعمال المنشورة من الداتابيز (المميّز الأول، ثم الترتيب).
 */
class PortfolioController extends Controller
{
    protected function seo(): Seo
    {
        return app(Seo::class);
    }

    protected function crumbs(array $extra = []): array
    {
        return array_merge([
            ['label' => 'الرئيسية', 'url' => '/'],
            ['label' => 'أعمالنا', 'url' => '/portfolio'],
        ], $extra);
    }

    public function index(): View
    {
        $projects = Project::query()->published()
            ->orderByDesc('is_featured')
            ->orderBy('position')
            ->orderByDesc('completed_at')
            ->get();

        $items = $projects->map(fn (Project $p) => [
            'name' => $p->title,
            'url'  => '/portfolio/' . $p->slug,
        ])->all();

        $this->seo()
            ->title('معرض أعمالنا — نماذج حقيقية من شغل خدماتي')
            ->description('شوف نماذج حقيقية من أعمال خدماتي في تركيب وصيانة وإصلاح التكييفات — خبرة أكتر من ' . (int) site('experience_years', 15) . ' سنة في ' . site('areas_label') . '.')
            ->canonical('/portfolio')
            ->robots(empty($items) ? 'noindex,follow' : 'index,follow');

        return view('pages.portfolio-index', [
            'projects'    => $projects,
            'items'       => $items,
            'breadcrumbs' => $this->crumbs(),
        ]);
    }

    public function show(Project $project): View
    {
        $path = '/portfolio/' . $project->slug;

        $this->seo()
            ->title($project->title . ' — أعمالنا')
            ->description($project->summary ?: ('نموذج من أعمال خدماتي: ' . $project->title))
            ->canonical($path)
            ->ogType('article')
            ->robots('index,follow');

        $related = Project::query()->published()
            ->whereKeyNot($project->getKey())
            ->orderByDesc('is_featured')->orderBy('position')
            ->take(3)->get();

        return view('pages.portfolio-item', [
            'project'     => $project,
            'related'     => $related,
            'breadcrumbs' => $this->crumbs([['label' => $project->title, 'url' => $path]]),
        ]);
    }
}
