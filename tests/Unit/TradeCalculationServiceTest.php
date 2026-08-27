<?php

namespace Tests\Unit;

use App\Services\TradeCalculationService;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class TradeCalculationServiceTest extends TestCase
{
    public function test_calculates_buy_trade_risk_reward_and_profit(): void
    {
        $service = new TradeCalculationService;

        $calculation = $service->calculate('buy', 3350.00, 3345.00, 3360.00, 0.10);

        $this->assertSame(['risk_amount' => 50.0, 'potential_profit' => 100.0, 'risk_reward' => 2.0], $calculation);
        $this->assertSame(30.0, $service->calculateProfitLoss('buy', 3350.00, 3353.00, 0.10));
    }

    public function test_calculates_sell_trade_risk_reward_and_loss(): void
    {
        $service = new TradeCalculationService;

        $calculation = $service->calculate('sell', 3350.00, 3355.00, 3340.00, 0.10);

        $this->assertSame(['risk_amount' => 50.0, 'potential_profit' => 100.0, 'risk_reward' => 2.0], $calculation);
        $this->assertSame(-30.0, $service->calculateProfitLoss('sell', 3350.00, 3353.00, 0.10));
    }

    public function test_rejects_invalid_buy_levels(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('For a BUY trade, Stop Loss must be below Entry Price.');

        (new TradeCalculationService)->calculate('buy', 3350.00, 3351.00, 3360.00, 0.10);
    }
}
