<?php

namespace Tests\Unit;

use App\Support\PhoneNumber;
use PHPUnit\Framework\TestCase;

/**
 * M6 — تطبيع الموبايل الموحّد: صيغ مختلفة لنفس الرقم لازم تطلع واحدة.
 */
class PhoneNumberTest extends TestCase
{
    public function test_local_format_is_unchanged(): void
    {
        $this->assertSame('01012345678', PhoneNumber::normalize('01012345678'));
    }

    public function test_international_plus_prefix_is_localized(): void
    {
        $this->assertSame('01012345678', PhoneNumber::normalize('+201012345678'));
    }

    public function test_country_code_without_plus_is_localized(): void
    {
        $this->assertSame('01012345678', PhoneNumber::normalize('201012345678'));
    }

    public function test_spaces_and_symbols_are_stripped(): void
    {
        $this->assertSame('01012345678', PhoneNumber::normalize('0101 234 5678'));
        $this->assertSame('01012345678', PhoneNumber::normalize('+20 101 234 5678'));
        $this->assertSame('01012345678', PhoneNumber::normalize('(010) 1234-5678'));
    }

    public function test_blank_returns_null(): void
    {
        $this->assertNull(PhoneNumber::normalize(null));
        $this->assertNull(PhoneNumber::normalize('   '));
    }

    public function test_all_forms_resolve_to_same_canonical(): void
    {
        $forms = ['01012345678', '+201012345678', '201012345678', '0101 234 5678'];
        $normalized = array_map(fn ($p) => PhoneNumber::normalize($p), $forms);

        $this->assertCount(1, array_unique($normalized));
    }
}
