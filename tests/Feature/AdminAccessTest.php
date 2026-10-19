<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Models\User;
use App\Support\Settings;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * صلاحيات لوحة الإدارة (أدمن/محرر) + تدفّق الحجز (المرحلة C3).
 */
class AdminAccessTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Settings::flush();
        $this->seed();
    }

    public function test_admin_can_reach_admin_only_resources(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)->get('/admin/users')->assertStatus(200);
        $this->actingAs($admin)->get('/admin/redirects')->assertStatus(200);
    }

    public function test_editor_is_blocked_from_admin_only_resources(): void
    {
        $editor = User::factory()->create(['role' => 'editor']);

        // المحرّر ممنوع من الإعدادات والمستخدمين والـ redirects.
        $this->actingAs($editor)->get('/admin/users')->assertStatus(403);
        $this->actingAs($editor)->get('/admin/redirects')->assertStatus(403);
        $this->actingAs($editor)->get('/admin/menu-items')->assertStatus(403);

        // لكن يقدر يوصل للمحتوى.
        $this->actingAs($editor)->get('/admin/services')->assertStatus(200);
    }

    public function test_booking_form_creates_a_record(): void
    {
        $payload = [
            'name' => 'عميل تجربة',
            'phone' => '01000000000',
            'area' => 'التجمع الخامس',
            'service' => 'صيانة دورية',
            'devices_count' => 1,
        ];

        $this->post('/booking', $payload)->assertRedirect();

        $this->assertDatabaseHas('bookings', [
            'name' => 'عميل تجربة',
            'service' => 'صيانة دورية',
            'status' => 'new',
        ]);
    }
}
