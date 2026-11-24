<?php

namespace Tests\Unit\Clinical;

use App\Services\Clinical\FdiToothService;
use PHPUnit\Framework\TestCase;

class FdiToothServiceTest extends TestCase
{
    public function test_valid_permanent_fdi_codes_are_accepted(): void
    {
        $service = new FdiToothService();

        $this->assertTrue(
            $service->isValid('permanent', '11')
        );

        $this->assertTrue(
            $service->isValid('permanent', '46')
        );

        $this->assertTrue(
            $service->isValid('permanent', '28')
        );
    }

    public function test_invalid_permanent_code_is_rejected(): void
    {
        $service = new FdiToothService();

        $this->assertFalse(
            $service->isValid('permanent', '19')
        );

        $this->assertFalse(
            $service->isValid('permanent', '51')
        );
    }

    public function test_valid_primary_codes_are_accepted(): void
    {
        $service = new FdiToothService();

        $this->assertTrue(
            $service->isValid('primary', '51')
        );

        $this->assertTrue(
            $service->isValid('primary', '85')
        );
    }

    public function test_invalid_primary_codes_are_rejected(): void
    {
        $service = new FdiToothService();

        $this->assertFalse(
            $service->isValid('primary', '56')
        );

        $this->assertFalse(
            $service->isValid('primary', '11')
        );
    }
}