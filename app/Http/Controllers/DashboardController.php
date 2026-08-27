<?php

namespace App\Http\Controllers;

use App\Models\EntryType;
use App\Models\TradeMistake;
use App\Models\TradeReason;
use App\Services\TradingDashboardService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(Request $request, TradingDashboardService $dashboard): View
    {
        $filters = $request->validate([
            'period' => ['nullable', Rule::in(['today', 'yesterday', 'this_week', 'last_week', 'this_month', 'last_month', 'this_year', 'custom'])],
            'date_from' => ['nullable', 'date', 'required_if:period,custom'],
            'date_to' => ['nullable', 'date', 'required_if:period,custom', 'after_or_equal:date_from'],
            'direction' => ['nullable', Rule::in(['buy', 'sell'])],
            'timeframe' => ['nullable', Rule::in(['15m', '30m', '1h', '4h'])],
            'trade_reason_id' => ['nullable', 'exists:trade_reasons,id'],
            'entry_type_id' => ['nullable', 'exists:entry_types,id'],
            'market_condition' => ['nullable', Rule::in(['trending', 'ranging', 'consolidating', 'volatile', 'choppy', 'clean'])],
            'trade_mistake_id' => ['nullable', 'exists:trade_mistakes,id'],
            'status' => ['nullable', Rule::in(['open', 'win', 'loss', 'breakeven'])],
        ]);

        $reasons = TradeReason::query()->where('is_active', true)->orderBy('name')->get(['id', 'name']);
        $entryTypes = EntryType::query()->where('is_active', true)->orderBy('name')->get(['id', 'name']);
        $mistakes = TradeMistake::query()->where('is_active', true)->orderBy('name')->get(['id', 'name']);

        return view('dashboard', [
            'analytics' => $dashboard->build($filters),
            'filters' => $filters,
            'activeFilters' => $this->activeFilters($filters, $reasons, $entryTypes, $mistakes),
            'reasons' => $reasons,
            'entryTypes' => $entryTypes,
            'mistakes' => $mistakes,
        ]);
    }

    private function activeFilters(array $filters, Collection $reasons, Collection $entryTypes, Collection $mistakes): array
    {
        $activeFilters = [];
        $periodLabels = ['today' => 'Today', 'yesterday' => 'Yesterday', 'this_week' => 'This Week', 'last_week' => 'Last Week', 'this_month' => 'This Month', 'last_month' => 'Last Month', 'this_year' => 'This Year'];

        if (($filters['period'] ?? null) === 'custom') {
            $activeFilters['Date'] = Carbon::parse($filters['date_from'])->format('M j, Y').' – '.Carbon::parse($filters['date_to'])->format('M j, Y');
        } elseif (isset($periodLabels[$filters['period'] ?? ''])) {
            $activeFilters['Date'] = $periodLabels[$filters['period']];
        }

        foreach (['direction' => 'Direction', 'timeframe' => 'Timeframe', 'market_condition' => 'Market Condition', 'status' => 'Result'] as $key => $label) {
            if (! empty($filters[$key])) {
                $activeFilters[$label] = strtoupper($filters[$key]);
            }
        }

        foreach ([['trade_reason_id', 'Setup', $reasons], ['entry_type_id', 'Entry Type', $entryTypes], ['trade_mistake_id', 'Trade Mistake', $mistakes]] as [$key, $label, $items]) {
            if (! empty($filters[$key]) && ($item = $items->firstWhere('id', (int) $filters[$key])) !== null) {
                $activeFilters[$label] = $item->name;
            }
        }

        return $activeFilters;
    }
}
