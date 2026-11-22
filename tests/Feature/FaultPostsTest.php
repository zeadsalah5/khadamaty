<?php

namespace Tests\Feature;

use App\Models\Post;
use App\Support\Settings;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class FaultPostsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Settings::flush();
        $this->seed();
        Cache::flush();
    }

    public function test_all_fault_posts_save_as_ready_drafts_without_todo(): void
    {
        $slugs = array_keys(require database_path('seeders/data/fault_posts.php'));

        $this->artisan('khadamaty:publish-content', ['type' => 'post', 'key' => implode(',', $slugs), '--draft' => true])
            ->assertSuccessful();

        foreach ($slugs as $slug) {
            $post = Post::where('slug', $slug)->firstOrFail();
            $this->assertFalse((bool) $post->is_published, $slug);
            $this->assertTrue($post->canPublish(), $slug . ': ' . implode('، ', $post->publishBlockers()));
            $this->assertNotNull($post->category_id, $slug);
            $this->get('/blog/' . $slug)->assertNotFound();
        }
    }

    public function test_published_fault_post_renders_with_source_and_internal_link(): void
    {
        $this->artisan('khadamaty:publish-content', ['type' => 'post', 'key' => 'sharp-ac-error-9-5'])->assertSuccessful();

        $this->get('/blog/sharp-ac-error-9-5')->assertOk()
            ->assertSee('المحابس مقفولة')
            ->assertSee('global.sharp/smartapp/air/support/airconerror', false)
            ->assertSee('/brands/sharp/ac-installation', false)
            ->assertDontSee('TODO');
    }
}
