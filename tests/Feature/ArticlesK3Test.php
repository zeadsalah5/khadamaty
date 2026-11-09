<?php

namespace Tests\Feature;

use App\Models\Post;
use App\Models\User;
use App\Support\Settings;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

/**
 * K3 — مقالات من أسئلة الناس (الهيكل الأول).
 * كلها مسودات محتاجة مراجعة قبل النشر؛ المواضيع الموجودة (زي عدم التبريد) مش بتتكرّر.
 */
class ArticlesK3Test extends TestCase
{
    use RefreshDatabase;

    /** @var list<string> */
    protected array $k3 = [
        'best-ac-types-egypt', 'how-to-clean-ac', 'ac-service-costs',
        'ac-compressor-repair', 'central-ac-maintenance-guide', 'how-to-choose-ac-technician',
    ];

    protected array $full = ['best-ac-types-egypt', 'how-to-clean-ac', 'ac-service-costs'];

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

    public function test_k3_articles_are_drafts_and_404_for_guest(): void
    {
        foreach ($this->k3 as $slug) {
            $post = Post::where('slug', $slug)->first();
            $this->assertNotNull($post, "المقال {$slug} المفروض متزرع.");
            $this->assertFalse($post->is_published, "{$slug} المفروض مسودة.");
            $this->assertSame(Post::STATUS_DRAFT, $post->content_status);

            $this->get('/blog/' . $slug)->assertNotFound(); // مسودة => 404 للزائر
        }
    }

    public function test_full_articles_have_body_direct_answer_and_internal_links(): void
    {
        foreach ($this->full as $slug) {
            $post = Post::where('slug', $slug)->firstOrFail();
            $body = (string) $post->body;

            $this->assertNotEmpty($body, "{$slug} المفروض ليه body كامل.");
            $this->assertStringContainsString('الإجابة المباشرة', $body, 'لازم إجابة مباشرة في الأول.');
            $this->assertStringContainsString('## ', $body, 'لازم عناوين H2.');
            $this->assertStringContainsString('## أسئلة شائعة', $body, 'لازم قسم FAQ.');
            $this->assertStringContainsString('/services/', $body, 'لازم رابط داخلي لخدمة.');
        }
    }

    public function test_full_article_renders_for_admin_preview_with_noindex(): void
    {
        $res = $this->actingAs($this->admin())->get('/blog/best-ac-types-egypt');

        $res->assertOk();
        $res->assertSee('مسودة', false);
        $res->assertHeader('X-Robots-Tag', 'noindex, nofollow');
        $res->assertSee('أفضل أنواع التكييفات', false);
        $res->assertSee('Article', false); // Article schema من قالب المقال
    }

    public function test_existing_not_cooling_article_not_duplicated(): void
    {
        // موضوع «عدم التبريد» مغطّى بمقال موجود واحد بس — مفيش تكرار.
        $this->assertSame(1, Post::where('slug', 'ac-not-cooling')->count());
        $this->assertTrue((bool) Post::where('slug', 'ac-not-cooling')->value('is_published'));
    }
}
