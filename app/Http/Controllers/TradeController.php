<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreTradeRequest;
use App\Http\Requests\UpdateTradeRequest;
use App\Models\EntryType;
use App\Models\Trade;
use App\Models\TradeMistake;
use App\Models\TradeReason;
use App\Services\TradeCalculationService;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TradeController extends Controller
{
    public function __construct(private TradeCalculationService $calculator) {}

    public function index(Request $request): View
    {
        $query = Trade::query()->with(['reason', 'entryType', 'mistake']);

        if ($request->filled('date')) {
            $query->whereDate('trade_date', $request->date);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('trade_date', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('trade_date', '<=', $request->date_to);
        }

        foreach (['direction', 'timeframe', 'trade_reason_id', 'entry_type_id', 'market_condition', 'status'] as $filter) {
            if ($request->filled($filter)) {
                $query->where($filter, $request->input($filter));
            }
        }

        $trades = $query
            ->latest('trade_date')
            ->latest('trade_time')
            ->latest('id')
            ->paginate(20)
            ->withQueryString();

        $reasons = TradeReason::query()->where('is_active', true)->orderBy('name')->get();
        $entryTypes = EntryType::query()->where('is_active', true)->orderBy('name')->get();

        return view('trades.index', compact('trades', 'reasons', 'entryTypes'));
    }

    public function create(): View
    {
        return view('trades.create', $this->formOptions(true));
    }

    public function store(StoreTradeRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $calculation = $this->calculationFor($data);

        Trade::create($this->tradeAttributes($data, $calculation, null));

        return redirect()->route('trades.index')->with('success', 'Trade added successfully.');
    }

    public function show(Trade $trade): View
    {
        $trade->load(['reason', 'entryType', 'mistake', 'images']);

        return view('trades.show', compact('trade'));
    }

    public function edit(Trade $trade): View
    {
        return view('trades.edit', array_merge(['trade' => $trade], $this->formOptions(true, $trade->trade_mistake_id)));
    }

    public function update(UpdateTradeRequest $request, Trade $trade): RedirectResponse
    {
        $data = $request->validated();
        $calculation = $this->calculationFor($data);
        $actualProfitLoss = null;
        $status = 'open';

        if (! empty($data['exit_price'])) {
            $actualProfitLoss = $this->calculator->calculateProfitLoss(
                $data['direction'],
                (float) $data['entry_price'],
                (float) $data['exit_price'],
                (float) $data['lot_size'],
                100,
            );
            $status = $this->calculator->determineStatus($actualProfitLoss);
        }

        $trade->update($this->tradeAttributes($data, $calculation, $actualProfitLoss, $status));

        return redirect()->route('trades.show', $trade)->with('success', 'Trade updated successfully.');
    }

    public function destroy(Trade $trade): RedirectResponse
    {
        $trade->delete();

        return redirect()->route('trades.index')->with('success', 'Trade deleted successfully.');
    }

    private function formOptions(bool $withMistakes = false, ?int $selectedMistakeId = null): array
    {
        $options = [
            'reasons' => TradeReason::query()->where('is_active', true)->orderBy('name')->get(),
            'entryTypes' => EntryType::query()->where('is_active', true)->orderBy('name')->get(),
        ];

        if ($withMistakes) {
            $options['mistakes'] = TradeMistake::query()
                ->where(function ($query) use ($selectedMistakeId) {
                    $query->where('is_active', true);

                    if ($selectedMistakeId !== null) {
                        $query->orWhereKey($selectedMistakeId);
                    }
                })
                ->orderBy('name')
                ->get();
        }

        return $options;
    }

    private function calculationFor(array $data): array
    {
        return $this->calculator->calculate(
            $data['direction'],
            (float) $data['entry_price'],
            (float) $data['stop_loss'],
            (float) $data['take_profit'],
            (float) $data['lot_size'],
            100,
        );
    }

    private function tradeAttributes(
        array $data,
        array $calculation,
        ?float $actualProfitLoss,
        string $status = 'open',
    ): array {
        $tradeDate = Carbon::parse($data['trade_date']);

        return [
            'trade_reason_id' => $data['trade_reason_id'],
            'trade_mistake_id' => $data['trade_mistake_id'] ?? null,
            'entry_type_id' => $data['entry_type_id'],
            'symbol' => 'XAUUSD',
            'trade_date' => $tradeDate,
            'trade_time' => $data['trade_time'] ?? null,
            'weekday' => $tradeDate->format('l'),
            'direction' => $data['direction'],
            'timeframe' => $data['timeframe'],
            'market_condition' => $data['market_condition'],
            'entry_price' => $data['entry_price'],
            'stop_loss' => $data['stop_loss'],
            'take_profit' => $data['take_profit'],
            'exit_price' => $data['exit_price'] ?? null,
            'lot_size' => $data['lot_size'],
            'contract_size' => 100,
            'risk_amount' => $calculation['risk_amount'],
            'potential_profit' => $calculation['potential_profit'],
            'risk_reward' => $calculation['risk_reward'],
            'actual_profit_loss' => $actualProfitLoss,
            'status' => $status,
            'notes' => $data['notes'] ?? null,
            'why_entered' => $data['why_entered'] ?? null,
            'expected_scenario' => $data['expected_scenario'] ?? null,
            'confirmation_seen' => $data['confirmation_seen'] ?? null,
            'plan_vs_reality' => $data['plan_vs_reality'] ?? null,
            'execution_rating' => $data['execution_rating'] ?? null,
            'what_went_well' => $data['what_went_well'] ?? null,
            'what_went_wrong' => $data['what_went_wrong'] ?? null,
            'lesson_learned' => $data['lesson_learned'] ?? null,
        ];
    }
}
