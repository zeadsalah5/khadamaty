<?php

namespace Tests\Feature;

use App\Models\Post;
use App\Support\Settings;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class ServicePostsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Settings::flush();
        $this->seed();
        Cache::flush();
    }

    public function test_all_service_posts_publish_and_link_to_their_service(): void
    {
        $data = require database_path('seeders/data/service_posts.php');

        $this->artisan('khadamaty:publish-content', ['type' => 'post', 'key' => implode(',', array_keys($data)), '--ack-similarity' => true])
            ->assertSuccessful();

        foreach ($data as $slug => $row) {
            $post = Post::where('slug', $slug)->firstOrFail();
            $this->assertTrue((bool) $post->is_published, $slug);
            $this->assertLessThanOrEqual(60, mb_strlen($row['seo_title']), $slug);
            $this->get('/blog/' . $slug)->assertOk()
                ->assertSee('/services/' . $row['suitable_services'][0], false)
                ->assertDontSee('TODO');
        }
    }
}
