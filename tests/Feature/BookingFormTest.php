<?php

namespace Tests\Feature;

use App\Models\Booking;
use App\Support\Settings;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

/**
 * فورم الحجز: الطلب لازم يوصل أو العميل يشوف ليه ماوصلش.
 */
class BookingFormTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Settings::flush();
        Cache::flush();
    }

    private function payload(array $override = []): array
    {
        return array_merge([
            'name' => 'محمد أحمد', 'phone' => '01012345678',
            'area' => 'المعادي', 'service' => 'تركيب تكييف', 'devices_count' => 1,
        ], $override);
    }

    public function test_common_egyptian_phone_formats_are_accepted(): void
    {
        foreach (['+20 101 234 5678', '0020-101-234-5678', '٠١٠١٢٣٤٥٦٧٨', '1012345678', '010 1234 5678'] as $i => $phone) {
            $this->from('/prices')->post('/booking', $this->payload(['phone' => $phone, 'name' => 'عميل رقم ' . $i]))
                ->assertSessionHasNoErrors();
        }

        $this->assertSame(5, Booking::count());
        $this->assertSame(['01012345678'], Booking::distinct()->pluck('phone')->all());
    }

    public function test_validation_error_returns_to_the_form_anchor(): void
    {
        $this->from('/services/ac-installation')->post('/booking', $this->payload(['phone' => '123']))
            ->assertRedirect('/services/ac-installation#booking')
            ->assertSessionHasErrors('phone');

        $this->assertSame(0, Booking::count());
    }

    public function test_success_returns_to_the_form_anchor(): void
    {
        $this->from('/prices')->post('/booking', $this->payload())
            ->assertRedirect('/prices#booking')
            ->assertSessionHas('booking_success');
    }
}
