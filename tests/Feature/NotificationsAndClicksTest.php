<?php

namespace Tests\Feature;

use App\Filament\Pages\ManageNotifications;
use App\Mail\NewBookingNotification;
use App\Models\ContactClick;
use App\Models\User;
use App\Support\MailSettings;
use App\Support\Settings;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

/**
 * إشعارات الطلبات/الحجوزات بالإيميل + إعدادات SMTP من الداشبورد،
 * وتتبّع ضغطات أزرار التواصل (اتصال/واتساب).
 */
class NotificationsAndClicksTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    private function validBooking(): array
    {
        return [
            'name'    => 'عميل تجريبي',
            'phone'   => '01000000000',
            'area'    => 'المعادي',
            'service' => 'صيانة دورية',
        ];
    }

    public function test_booking_sends_email_when_recipient_set(): void
    {
        Mail::fake();
        Settings::set('order_notify_email', 'owner@gmail.com', 'company');

        $this->post('/booking', $this->validBooking());

        Mail::assertSent(NewBookingNotification::class, fn ($mail) => $mail->hasTo('owner@gmail.com'));
    }

    public function test_booking_sends_no_email_when_recipient_empty(): void
    {
        Mail::fake();
        Settings::set('order_notify_email', '', 'company');

        $this->post('/booking', $this->validBooking());

        Mail::assertNothingSent();
    }

    public function test_call_and_whatsapp_clicks_are_recorded(): void
    {
        $this->post('/t/click?type=call')->assertNoContent();
        $this->post('/t/click?type=whatsapp&path=/services')->assertNoContent();

        $this->assertSame(1, ContactClick::where('type', 'call')->count());
        $this->assertSame(1, ContactClick::where('type', 'whatsapp')->count());
        $this->assertSame('/services', ContactClick::where('type', 'whatsapp')->first()->path);
    }

    public function test_invalid_click_type_is_ignored(): void
    {
        $this->post('/t/click?type=hack')->assertNoContent();

        $this->assertSame(0, ContactClick::count());
    }

    public function test_mail_settings_apply_to_config(): void
    {
        Settings::setMany([
            'mail_enabled'    => true,
            'mail_host'       => 'smtp.example.com',
            'mail_port'       => 465,
            'mail_username'   => 'no-reply@example.com',
            'mail_encryption' => 'ssl',
        ], 'mail');
        Settings::set('mail_password', Crypt::encryptString('secret-pass'), 'mail');

        MailSettings::apply();

        $this->assertSame('smtp', config('mail.default'));
        $this->assertSame('smtp.example.com', config('mail.mailers.smtp.host'));
        $this->assertSame(465, config('mail.mailers.smtp.port'));
        $this->assertSame('smtps', config('mail.mailers.smtp.scheme'));
        $this->assertSame('secret-pass', config('mail.mailers.smtp.password'));
    }

    public function test_mail_settings_ignored_when_disabled(): void
    {
        config(['mail.default' => 'log']);
        Settings::setMany(['mail_enabled' => false, 'mail_host' => 'smtp.example.com'], 'mail');

        MailSettings::apply();

        $this->assertSame('log', config('mail.default'));
    }

    public function test_admin_can_view_notifications_page(): void
    {
        $admin = User::where('role', 'admin')->firstOrFail();

        $this->actingAs($admin)
            ->get(ManageNotifications::getUrl())
            ->assertOk()
            ->assertSee('الإشعارات والبريد');
    }
}
