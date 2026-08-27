<?php

namespace App\Services;

use InvalidArgumentException;

class TradeCalculationService
{
    /**
     * Calculate XAUUSD trade values.
     */
    public function calculate(
        string $direction,
        float $entryPrice,
        float $stopLoss,
        float $takeProfit,
        float $lotSize,
        float $contractSize = 100
    ): array {
        $direction = strtolower($direction);

        $this->validateDirection($direction);

        $this->validatePrices(
            $direction,
            $entryPrice,
            $stopLoss,
            $takeProfit
        );

        $this->validateLotSize($lotSize);

        $riskDistance = $this->calculateRiskDistance(
            $direction,
            $entryPrice,
            $stopLoss
        );

        $rewardDistance = $this->calculateRewardDistance(
            $direction,
            $entryPrice,
            $takeProfit
        );

        $riskAmount = $riskDistance * $contractSize * $lotSize;

        $potentialProfit = $rewardDistance * $contractSize * $lotSize;

        $riskReward = $riskDistance > 0
            ? $rewardDistance / $riskDistance
            : 0;

        return [
            'risk_amount' => round($riskAmount, 2),

            'potential_profit' => round(
                $potentialProfit,
                2
            ),

            'risk_reward' => round(
                $riskReward,
                2
            ),
        ];
    }

    /**
     * Calculate actual P/L after trade is closed.
     */
    public function calculateProfitLoss(
        string $direction,
        float $entryPrice,
        float $exitPrice,
        float $lotSize,
        float $contractSize = 100
    ): float {
        $direction = strtolower($direction);

        $this->validateDirection($direction);

        $priceDifference = match ($direction) {
            'buy' => $exitPrice - $entryPrice,
            'sell' => $entryPrice - $exitPrice,
        };

        return round(
            $priceDifference * $contractSize * $lotSize,
            2
        );
    }

    /**
     * Determine trade status from P/L.
     */
    public function determineStatus(float $profitLoss): string
    {
        return match (true) {
            $profitLoss > 0 => 'win',
            $profitLoss < 0 => 'loss',
            default => 'breakeven',
        };
    }

    private function calculateRiskDistance(
        string $direction,
        float $entryPrice,
        float $stopLoss
    ): float {
        return match ($direction) {
            'buy' => $entryPrice - $stopLoss,
            'sell' => $stopLoss - $entryPrice,
        };
    }

    private function calculateRewardDistance(
        string $direction,
        float $entryPrice,
        float $takeProfit
    ): float {
        return match ($direction) {
            'buy' => $takeProfit - $entryPrice,
            'sell' => $entryPrice - $takeProfit,
        };
    }

    private function validateDirection(string $direction): void
    {
        if (! in_array($direction, ['buy', 'sell'], true)) {
            throw new InvalidArgumentException(
                'Direction must be either buy or sell.'
            );
        }
    }

    private function validatePrices(
        string $direction,
        float $entryPrice,
        float $stopLoss,
        float $takeProfit
    ): void {
        if ($entryPrice <= 0 || $stopLoss <= 0 || $takeProfit <= 0) {
            throw new InvalidArgumentException(
                'Prices must be greater than zero.'
            );
        }

        if ($direction === 'buy') {
            if ($stopLoss >= $entryPrice) {
                throw new InvalidArgumentException(
                    'For a BUY trade, Stop Loss must be below Entry Price.'
                );
            }

            if ($takeProfit <= $entryPrice) {
                throw new InvalidArgumentException(
                    'For a BUY trade, Take Profit must be above Entry Price.'
                );
            }
        }

        if ($direction === 'sell') {
            if ($stopLoss <= $entryPrice) {
                throw new InvalidArgumentException(
                    'For a SELL trade, Stop Loss must be above Entry Price.'
                );
            }

            if ($takeProfit >= $entryPrice) {
                throw new InvalidArgumentException(
                    'For a SELL trade, Take Profit must be below Entry Price.'
                );
            }
        }
    }

    private function validateLotSize(float $lotSize): void
    {
        if ($lotSize < 0.01) {
            throw new InvalidArgumentException(
                'Lot size must be at least 0.01.'
            );
        }
    }
}
