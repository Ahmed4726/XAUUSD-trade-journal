<?php

namespace App\Services;

use App\Models\EntryType;
use App\Models\Trade;
use App\Models\TradeMistake;
use App\Models\TradeReason;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class TradingDashboardService
{
    public function build(array $filters): array
    {
        $trades = $this->filteredTrades($filters);
        $kpis = $this->metrics($trades);
        $reasons = TradeReason::query()->where('is_active', true)->orderBy('name')->get(['id', 'name']);
        $entryTypes = EntryType::query()->where('is_active', true)->orderBy('name')->get(['id', 'name']);
        $mistakes = TradeMistake::query()->where('is_active', true)->orderBy('name')->get(['id', 'name']);

        $timeframes = $this->withExpectedCategories($this->groupedMetrics($trades, 'timeframe'), collect(['15m', '30m', '1h', '4h'])->map(fn (string $name): array => ['key' => $name, 'name' => strtoupper($name)]));
        $setups = $this->withExpectedCategories($this->relationshipMetrics($trades, 'trade_reasons', 'trade_reason_id'), $reasons->map(fn (TradeReason $reason): array => ['key' => $reason->id, 'name' => $reason->name]));
        $types = $this->withExpectedCategories($this->relationshipMetrics($trades, 'entry_types', 'entry_type_id'), $entryTypes->map(fn (EntryType $type): array => ['key' => $type->id, 'name' => $type->name]));
        $conditions = $this->withExpectedCategories($this->groupedMetrics($trades, 'market_condition'), collect(['trending', 'ranging', 'consolidating', 'volatile', 'choppy', 'clean'])->map(fn (string $name): array => ['key' => $name, 'name' => ucfirst($name)]));
        $weekdays = $this->withExpectedCategories($this->groupedMetrics($trades, 'weekday'), collect(['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday'])->map(fn (string $name): array => ['key' => $name, 'name' => $name]));
        $directions = $this->withExpectedCategories($this->groupedMetrics($trades, 'direction'), collect(['buy', 'sell'])->map(fn (string $name): array => ['key' => $name, 'name' => strtoupper($name)]));
        $mistakeAnalysis = $this->withExpectedCategories($this->relationshipMetrics($trades, 'trade_mistakes', 'trade_mistake_id', true), $mistakes->map(fn (TradeMistake $mistake): array => ['key' => $mistake->id, 'name' => $mistake->name]), true);
        $daily = $this->dailyMetrics($trades);
        $equityCurve = $this->equityCurve($trades);

        return [
            'kpis' => $kpis,
            'timeframes' => $timeframes,
            'setups' => $setups,
            'entryTypes' => $types,
            'conditions' => $conditions,
            'weekdays' => $weekdays,
            'directions' => $directions,
            'mistakes' => $mistakeAnalysis,
            'daily' => $daily,
            'equityCurve' => $equityCurve,
            'charts' => [
                'equity' => $equityCurve->map(fn (array $row): array => ['label' => $row['label'], 'value' => $row['cumulative_profit_loss']]),
                'daily' => $daily->map(fn (array $row): array => ['label' => $row['date'], 'value' => $row['net_profit_loss']]),
                'results' => ['Win' => $kpis['wins'], 'Loss' => $kpis['losses'], 'Breakeven' => $kpis['breakevens']],
                'timeframes' => $timeframes->map(fn (array $row): array => ['label' => $row['name'], 'value' => $row['net_profit_loss']]),
                'setups' => $setups->filter(fn (array $row): bool => $row['trades'] > 0)->map(fn (array $row): array => ['label' => $row['name'], 'value' => $row['net_profit_loss']]),
            ],
            'highlights' => [
                'bestSetup' => $this->best($setups),
                'worstSetup' => $this->worst($setups),
                'bestTimeframe' => $this->best($timeframes),
                'worstTimeframe' => $this->worst($timeframes),
                'bestWeekday' => $this->best($weekdays),
                'worstWeekday' => $this->worst($weekdays),
                'bestEntryType' => $this->best($types),
            ],
            'insights' => $this->insights($setups, $timeframes, $weekdays, $directions),
        ];
    }

    private function filteredTrades(array $filters): Builder
    {
        $query = Trade::query();

        foreach (['direction', 'timeframe', 'trade_reason_id', 'entry_type_id', 'market_condition', 'trade_mistake_id', 'status'] as $filter) {
            if (! empty($filters[$filter])) {
                $query->where($filter, $filters[$filter]);
            }
        }

        [$start, $end] = $this->dateRange($filters);

        if ($start !== null) {
            $query->whereDate('trade_date', '>=', $start);
        }

        if ($end !== null) {
            $query->whereDate('trade_date', '<=', $end);
        }

        return $query;
    }

    private function dateRange(array $filters): array
    {
        $period = $filters['period'] ?? null;

        return match ($period) {
            'today' => [now()->toDateString(), now()->toDateString()],
            'yesterday' => [now()->subDay()->toDateString(), now()->subDay()->toDateString()],
            'this_week' => [now()->startOfWeek()->toDateString(), now()->endOfWeek()->toDateString()],
            'last_week' => [now()->subWeek()->startOfWeek()->toDateString(), now()->subWeek()->endOfWeek()->toDateString()],
            'this_month' => [now()->startOfMonth()->toDateString(), now()->endOfMonth()->toDateString()],
            'last_month' => [now()->subMonth()->startOfMonth()->toDateString(), now()->subMonth()->endOfMonth()->toDateString()],
            'this_year' => [now()->startOfYear()->toDateString(), now()->endOfYear()->toDateString()],
            'custom' => [$filters['date_from'] ?? null, $filters['date_to'] ?? null],
            default => [null, null],
        };
    }

    private function metrics(Builder $query): array
    {
        $row = (clone $query)->selectRaw("COUNT(*) as total_trades, SUM(CASE WHEN status = 'win' THEN 1 ELSE 0 END) as wins, SUM(CASE WHEN status = 'loss' THEN 1 ELSE 0 END) as losses, SUM(CASE WHEN status = 'breakeven' THEN 1 ELSE 0 END) as breakevens, SUM(COALESCE(actual_profit_loss, 0)) as net_profit_loss, SUM(COALESCE(risk_amount, 0)) as total_risk, AVG(CASE WHEN status <> 'open' THEN risk_reward END) as average_risk_reward, AVG(CASE WHEN status = 'win' THEN actual_profit_loss END) as average_winning_trade, AVG(CASE WHEN status = 'loss' THEN actual_profit_loss END) as average_losing_trade, SUM(CASE WHEN actual_profit_loss > 0 THEN actual_profit_loss ELSE 0 END) as gross_profit, SUM(CASE WHEN actual_profit_loss < 0 THEN -actual_profit_loss ELSE 0 END) as gross_loss")->first();

        return $this->metricRow($row?->getAttributes() ?? []);
    }

    private function groupedMetrics(Builder $query, string $column): Collection
    {
        return (clone $query)->selectRaw("{$column} as category_key, {$column} as category_name, COUNT(*) as total_trades, SUM(CASE WHEN status = 'win' THEN 1 ELSE 0 END) as wins, SUM(CASE WHEN status = 'loss' THEN 1 ELSE 0 END) as losses, SUM(COALESCE(actual_profit_loss, 0)) as net_profit_loss, AVG(CASE WHEN status <> 'open' THEN risk_reward END) as average_risk_reward, SUM(CASE WHEN actual_profit_loss > 0 THEN actual_profit_loss ELSE 0 END) as gross_profit, SUM(CASE WHEN actual_profit_loss < 0 THEN -actual_profit_loss ELSE 0 END) as gross_loss")
            ->groupBy($column)
            ->get()
            ->map(fn (Trade $row): array => $this->metricRow($row->getAttributes(), ['key' => $row->category_key, 'name' => $row->category_name]));
    }

    private function relationshipMetrics(Builder $query, string $table, string $foreignKey, bool $includeNull = false): Collection
    {
        $base = (clone $query)->leftJoin($table, "trades.{$foreignKey}", '=', "{$table}.id");
        $rows = $base->selectRaw("{$table}.id as category_key, COALESCE({$table}.name, 'No mistake recorded') as category_name, COUNT(*) as total_trades, SUM(CASE WHEN trades.status = 'win' THEN 1 ELSE 0 END) as wins, SUM(CASE WHEN trades.status = 'loss' THEN 1 ELSE 0 END) as losses, SUM(COALESCE(trades.actual_profit_loss, 0)) as net_profit_loss, AVG(CASE WHEN trades.status <> 'open' THEN trades.risk_reward END) as average_risk_reward, SUM(CASE WHEN trades.actual_profit_loss > 0 THEN trades.actual_profit_loss ELSE 0 END) as gross_profit, SUM(CASE WHEN trades.actual_profit_loss < 0 THEN -trades.actual_profit_loss ELSE 0 END) as gross_loss")
            ->groupBy("{$table}.id", "{$table}.name")
            ->get()
            ->map(fn (Trade $row): array => $this->metricRow($row->getAttributes(), ['key' => $row->category_key, 'name' => $row->category_name]));

        return $includeNull ? $rows : $rows->whereNotNull('key')->values();
    }

    private function withExpectedCategories(Collection $metrics, Collection $categories, bool $keepExtra = false): Collection
    {
        $indexed = $metrics->keyBy(fn (array $metric): string => (string) $metric['key']);
        $rows = $categories->map(function (array $category) use ($indexed): array {
            return $indexed->get((string) $category['key'], $this->emptyMetric($category));
        });

        if ($keepExtra) {
            $rows = $rows->concat($metrics->filter(fn (array $metric): bool => $metric['key'] === null));
        }

        return $rows->values();
    }

    private function dailyMetrics(Builder $query): Collection
    {
        return (clone $query)->selectRaw("trade_date as date, COUNT(*) as total_trades, SUM(CASE WHEN status = 'win' THEN 1 ELSE 0 END) as wins, SUM(CASE WHEN status = 'loss' THEN 1 ELSE 0 END) as losses, SUM(COALESCE(actual_profit_loss, 0)) as net_profit_loss")
            ->groupBy('trade_date')
            ->orderBy('trade_date')
            ->get()
            ->map(fn (Trade $row): array => [
                'date' => Carbon::parse($row->date)->toDateString(),
                'trades' => (int) $row->total_trades,
                'wins' => (int) $row->wins,
                'losses' => (int) $row->losses,
                'net_profit_loss' => (float) $row->net_profit_loss,
            ]);
    }

    private function equityCurve(Builder $query): Collection
    {
        $cumulative = 0.0;

        return (clone $query)->whereNotNull('actual_profit_loss')->orderBy('trade_date')->orderBy('trade_time')->orderBy('id')->get(['id', 'trade_date', 'actual_profit_loss'])
            ->map(function (Trade $trade) use (&$cumulative): array {
                $cumulative += (float) $trade->actual_profit_loss;

                return ['label' => $trade->trade_date->format('d M'), 'profit_loss' => (float) $trade->actual_profit_loss, 'cumulative_profit_loss' => round($cumulative, 2)];
            });
    }

    private function metricRow(array $row, array $identity = []): array
    {
        $wins = (int) ($row['wins'] ?? 0);
        $losses = (int) ($row['losses'] ?? 0);
        $grossLoss = (float) ($row['gross_loss'] ?? 0);
        $closed = $wins + $losses + (int) ($row['breakevens'] ?? 0);

        return array_merge($identity, [
            'trades' => (int) ($row['total_trades'] ?? 0),
            'wins' => $wins,
            'losses' => $losses,
            'breakevens' => (int) ($row['breakevens'] ?? 0),
            'win_rate' => $closed > 0 ? round(($wins / $closed) * 100, 2) : null,
            'net_profit_loss' => (float) ($row['net_profit_loss'] ?? 0),
            'total_risk' => (float) ($row['total_risk'] ?? 0),
            'average_risk_reward' => isset($row['average_risk_reward']) ? round((float) $row['average_risk_reward'], 2) : null,
            'average_winning_trade' => isset($row['average_winning_trade']) ? round((float) $row['average_winning_trade'], 2) : null,
            'average_losing_trade' => isset($row['average_losing_trade']) ? round((float) $row['average_losing_trade'], 2) : null,
            'profit_factor' => $grossLoss > 0 ? round(((float) ($row['gross_profit'] ?? 0)) / $grossLoss, 2) : null,
        ]);
    }

    private function emptyMetric(array $category): array
    {
        return array_merge($category, ['trades' => 0, 'wins' => 0, 'losses' => 0, 'breakevens' => 0, 'win_rate' => null, 'net_profit_loss' => 0.0, 'total_risk' => 0.0, 'average_risk_reward' => null, 'average_winning_trade' => null, 'average_losing_trade' => null, 'profit_factor' => null]);
    }

    private function insights(Collection $setups, Collection $timeframes, Collection $weekdays, Collection $directions): Collection
    {
        $insights = collect();
        $bestSetup = $setups->filter(fn (array $row): bool => $row['trades'] >= 5 && $row['win_rate'] !== null)->sortByDesc('win_rate')->first();
        $bestTimeframe = $timeframes->filter(fn (array $row): bool => $row['trades'] > 0)->sortByDesc('net_profit_loss')->first();
        $negativeWeekday = $weekdays->filter(fn (array $row): bool => $row['trades'] > 0 && $row['net_profit_loss'] < 0)->first();

        if ($bestSetup !== null) {
            $insights->push("{$bestSetup['name']} has the highest win rate among setups with at least 5 trades.");
        }
        if ($bestTimeframe !== null) {
            $insights->push("{$bestTimeframe['name']} has the highest net P/L.");
        }
        if ($directions->count() === 2 && $directions[0]['win_rate'] !== null && $directions[1]['win_rate'] !== null) {
            $insights->push(($directions[0]['win_rate'] >= $directions[1]['win_rate'] ? 'BUY' : 'SELL').' trades have the higher win rate.');
        }
        if ($negativeWeekday !== null) {
            $insights->push("{$negativeWeekday['name']} has negative net P/L.");
        }

        return $insights;
    }

    private function best(Collection $rows): ?array
    {
        return $rows->filter(fn (array $row): bool => $row['trades'] > 0)->sortByDesc('net_profit_loss')->first();
    }

    private function worst(Collection $rows): ?array
    {
        return $rows->filter(fn (array $row): bool => $row['trades'] > 0)->sortBy('net_profit_loss')->first();
    }
}
