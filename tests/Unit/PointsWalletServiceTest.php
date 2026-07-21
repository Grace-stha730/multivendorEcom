<?php

namespace Tests\Unit;

use App\Services\Wallet\PointsWalletService;
use PHPUnit\Framework\TestCase;

class PointsWalletServiceTest extends TestCase
{
    public function test_discount_is_capped_by_cart_total(): void
    {
        $service = new PointsWalletService();

        $this->assertSame(15.0, $service->discountForPoints(150, 100));
        $this->assertSame(100.0, $service->discountForPoints(5000, 100));
        $this->assertSame(0.0, $service->discountForPoints(-20, 100));
    }
}
