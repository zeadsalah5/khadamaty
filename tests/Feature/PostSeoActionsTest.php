<?php

namespace Tests\Feature;

use App\Models\Post;
use App\Support\Settings;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class PostSeoActionsTest extends TestCase
{
    use RefreshDatabase;

    public function test_updates_unedited_posts_and_keeps_owner_edits(): void
    {
        Settings::flush();
        $this->seed();
        Cache::flush();
        $data = require database_path('seeders/data/post_seo_actions.php');
        $this->artisan('khadamaty:publish-content', ['type' => 'post', 'key' => 'ac-maintenance-nasr-city-guide,ac-maintenance-maadi-guide', '--ack-similarity' => true])->assertSuccessful();

        // محاكاة: نصر لسه بالعنوان القديم، المعادي اتعدّلت يدوياً.
        Post::where('slug', 'ac-maintenance-nasr-city-guide')->update(['seo_title' => $data['ac-maintenance-nasr-city-guide']['old_title']]);
        Post::where('slug', 'ac-maintenance-maadi-guide')->update(['seo_title' => 'عنوان صاحب الموقع']);

        $this->artisan('khadamaty:post-seo')->assertSuccessful();

        $this->assertSame($data['ac-maintenance-nasr-city-guide']['seo_title'], Post::where('slug', 'ac-maintenance-nasr-city-guide')->value('seo_title'));
        $this->assertSame('عنوان صاحب الموقع', Post::where('slug', 'ac-maintenance-maadi-guide')->value('seo_title'));
    }
}
