@php($editing = isset($trade))
<form method="POST" action="{{ $action }}" class="space-y-6">
    @csrf
    @if($method !== 'POST') @method($method) @endif

    <section class="rounded-2xl border border-white/10 bg-slate-900/70 p-5 sm:p-6">
        <h2 class="text-lg font-semibold text-white">Trade Information</h2>
        <div class="mt-5 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
            <label for="trade_date" class="journal-label">Date<input type="date" name="trade_date" id="trade_date" value="{{ old('trade_date', $editing ? $trade->trade_date->format('Y-m-d') : now()->format('Y-m-d')) }}" class="journal-input mt-1 @error('trade_date') border-rose-400 @enderror" required></label>
            <label for="weekday" class="journal-label">Weekday<input id="weekday" class="journal-input mt-1 cursor-not-allowed bg-slate-800" readonly></label>
            <label for="trade_time" class="journal-label">Time<input type="time" name="trade_time" id="trade_time" value="{{ old('trade_time', $editing && $trade->trade_time ? substr($trade->trade_time, 0, 5) : '') }}" class="journal-input mt-1"></label>
            <label for="direction" class="journal-label">Direction<select name="direction" id="direction" class="journal-input mt-1" required><option value="">Choose direction</option>@foreach(['buy' => 'BUY', 'sell' => 'SELL'] as $value => $label)<option value="{{ $value }}" @selected(old('direction', $editing ? $trade->direction : '') === $value)>{{ $label }}</option>@endforeach</select></label>
            <label for="timeframe" class="journal-label">Timeframe<select name="timeframe" id="timeframe" class="journal-input mt-1" required><option value="">Choose timeframe</option>@foreach(['15m', '30m', '1h', '4h'] as $timeframe)<option value="{{ $timeframe }}" @selected(old('timeframe', $editing ? $trade->timeframe : '') === $timeframe)>{{ strtoupper($timeframe) }}</option>@endforeach</select></label>
        </div>
    </section>

    <section class="rounded-2xl border border-white/10 bg-slate-900/70 p-5 sm:p-6">
        <h2 class="text-lg font-semibold text-white">Price Action Analysis</h2>
        <div class="mt-5 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
            <label for="trade_reason_id" class="journal-label">Price Action Setup<select name="trade_reason_id" id="trade_reason_id" class="journal-input mt-1" required><option value="">Choose setup</option>@foreach($reasons as $reason)<option value="{{ $reason->id }}" @selected((string) old('trade_reason_id', $editing ? $trade->trade_reason_id : '') === (string) $reason->id)>{{ $reason->name }}</option>@endforeach</select></label>
            <label for="entry_type_id" class="journal-label">Entry Type<select name="entry_type_id" id="entry_type_id" class="journal-input mt-1" required><option value="">Choose entry type</option>@foreach($entryTypes as $entryType)<option value="{{ $entryType->id }}" @selected((string) old('entry_type_id', $editing ? $trade->entry_type_id : '') === (string) $entryType->id)>{{ $entryType->name }}</option>@endforeach</select></label>
            <label for="market_condition" class="journal-label">Market Condition<select name="market_condition" id="market_condition" class="journal-input mt-1" required><option value="">Choose condition</option>@foreach(['trending', 'ranging', 'consolidating', 'volatile', 'choppy', 'clean'] as $condition)<option value="{{ $condition }}" @selected(old('market_condition', $editing ? $trade->market_condition : '') === $condition)>{{ ucfirst($condition) }}</option>@endforeach</select></label>
            @foreach(['entry_price' => 'Entry Price', 'stop_loss' => 'Stop Loss', 'take_profit' => 'Take Profit'] as $field => $label)<label for="{{ $field }}" class="journal-label">{{ $label }}<input type="number" name="{{ $field }}" id="{{ $field }}" value="{{ old($field, $editing ? $trade->$field : '') }}" step="0.01" min="0.01" class="journal-input mt-1 @error($field) border-rose-400 @enderror" required></label>@endforeach
        </div>
    </section>

    <section class="rounded-2xl border border-white/10 bg-slate-900/70 p-5 sm:p-6">
        <h2 class="text-lg font-semibold text-white">Risk Management</h2>
        <div class="mt-5 grid gap-4 sm:grid-cols-2 lg:grid-cols-4"><label for="lot_size" class="journal-label">Lot Size<input type="number" name="lot_size" id="lot_size" value="{{ old('lot_size', $editing ? $trade->lot_size : '0.01') }}" step="0.01" min="0.01" class="journal-input mt-1 @error('lot_size') border-rose-400 @enderror" required></label></div>
        <div class="mt-5 grid gap-3 sm:grid-cols-3"><div class="calculation-card"><span>Risk</span><strong>$<span id="risk_amount">0.00</span></strong></div><div class="calculation-card"><span>Potential Profit</span><strong>$<span id="potential_profit">0.00</span></strong></div><div class="calculation-card"><span>Risk / Reward</span><strong id="risk_reward">—</strong></div></div>
    </section>

    <section class="rounded-2xl border border-white/10 bg-slate-900/70 p-5 sm:p-6">
        <h2 class="text-lg font-semibold text-white">Trade Result</h2><p class="mt-1 text-sm text-slate-400">Enter an exit price when the trade is closed. Result is calculated automatically.</p>
        <div class="mt-5 grid gap-4 sm:grid-cols-2"><label for="exit_price" class="journal-label">Exit Price<input type="number" name="exit_price" id="exit_price" value="{{ old('exit_price', $editing ? $trade->exit_price : '') }}" step="0.01" min="0.01" class="journal-input mt-1"></label><div class="journal-label">Result<div class="journal-input mt-1 flex items-center bg-slate-800 text-slate-400">{{ $editing ? strtoupper($trade->status) : 'OPEN until an exit price is recorded' }}</div></div></div>
    </section>

    <section class="rounded-2xl border border-white/10 bg-slate-900/70 p-5 sm:p-6">
        <h2 class="text-lg font-semibold text-white">Post-Trade Review</h2><p class="mt-1 text-sm text-slate-400">Optional personal notes—complete these whenever you review the trade.</p>
        <div class="mt-5 grid gap-4 sm:grid-cols-2">
            <label for="trade_mistake_id" class="journal-label">Trade Mistake<select name="trade_mistake_id" id="trade_mistake_id" class="journal-input mt-1"><option value="">No mistake recorded</option>@foreach($mistakes as $mistake)<option value="{{ $mistake->id }}" @selected((string) old('trade_mistake_id', $editing ? $trade->trade_mistake_id : '') === (string) $mistake->id)>{{ $mistake->name }}</option>@endforeach</select></label>
            <label for="execution_rating" class="journal-label">Execution Quality<select name="execution_rating" id="execution_rating" class="journal-input mt-1"><option value="">No execution rating recorded</option>@foreach([1 => '★☆☆☆☆ — Very Poor', 2 => '★★☆☆☆ — Poor', 3 => '★★★☆☆ — Average', 4 => '★★★★☆ — Good', 5 => '★★★★★ — Excellent'] as $value => $label)<option value="{{ $value }}" @selected((string) old('execution_rating', $editing ? $trade->execution_rating : '') === (string) $value)>{{ $value }} / 5 — {{ $label }}</option>@endforeach</select></label>
            <label for="why_entered" class="journal-label">Why Did I Enter?<textarea name="why_entered" id="why_entered" rows="5" placeholder="Describe the price action reason for the entry." class="journal-input mt-1">{{ old('why_entered', $editing ? $trade->why_entered : '') }}</textarea></label>
            <label for="confirmation_seen" class="journal-label">What Confirmation Did I See?<textarea name="confirmation_seen" id="confirmation_seen" rows="5" placeholder="Record the confirmation you saw before entering." class="journal-input mt-1">{{ old('confirmation_seen', $editing ? $trade->confirmation_seen : '') }}</textarea></label>
            <label for="expected_scenario" class="journal-label">Expected Scenario<textarea name="expected_scenario" id="expected_scenario" rows="5" placeholder="What did you expect price to do?" class="journal-input mt-1">{{ old('expected_scenario', $editing ? $trade->expected_scenario : '') }}</textarea></label>
            <label for="plan_vs_reality" class="journal-label">Plan vs Reality<textarea name="plan_vs_reality" id="plan_vs_reality" rows="5" placeholder="What actually happened compared with your plan?" class="journal-input mt-1">{{ old('plan_vs_reality', $editing ? $trade->plan_vs_reality : '') }}</textarea></label>
            <label for="what_went_well" class="journal-label">What Went Well?<textarea name="what_went_well" id="what_went_well" rows="5" placeholder="What did you execute well?" class="journal-input mt-1">{{ old('what_went_well', $editing ? $trade->what_went_well : '') }}</textarea></label>
            <label for="what_went_wrong" class="journal-label">What Went Wrong?<textarea name="what_went_wrong" id="what_went_wrong" rows="5" placeholder="What could have been better?" class="journal-input mt-1">{{ old('what_went_wrong', $editing ? $trade->what_went_wrong : '') }}</textarea></label>
            <label for="lesson_learned" class="journal-label sm:col-span-2">Lesson Learned<textarea name="lesson_learned" id="lesson_learned" rows="5" placeholder="Record the rule or lesson you want to carry forward." class="journal-input mt-1">{{ old('lesson_learned', $editing ? $trade->lesson_learned : '') }}</textarea></label>
            <label for="notes" class="journal-label sm:col-span-2">Additional Notes<textarea name="notes" id="notes" rows="4" placeholder="Any additional context for this trade." class="journal-input mt-1">{{ old('notes', $editing ? $trade->notes : '') }}</textarea></label>
        </div>
    </section>
    <div class="flex items-center justify-end gap-3"><a href="{{ $editing ? route('trades.show', $trade) : route('trades.index') }}" class="rounded-lg px-4 py-2.5 text-sm font-semibold text-slate-300 hover:bg-white/10">Cancel</a><button class="rounded-lg bg-amber-400 px-5 py-2.5 text-sm font-semibold text-slate-950 hover:bg-amber-300">{{ $submitLabel }}</button></div>
</form>

@push('scripts')
<script>
const fields = ['direction', 'entry_price', 'stop_loss', 'take_profit', 'lot_size'].map((id) => document.getElementById(id));
const dateInput = document.getElementById('trade_date'); const weekdayInput = document.getElementById('weekday');
const updateWeekday = () => { weekdayInput.value = dateInput.value ? new Date(`${dateInput.value}T00:00:00`).toLocaleDateString('en-US', { weekday: 'long' }) : ''; };
const calculateTrade = () => { const [direction, entry, stopLoss, takeProfit, lotSize] = [fields[0].value, ...fields.slice(1).map((field) => Number.parseFloat(field.value))]; if (!direction || [entry, stopLoss, takeProfit, lotSize].some(Number.isNaN)) return; const riskDistance = direction === 'buy' ? entry - stopLoss : stopLoss - entry; const rewardDistance = direction === 'buy' ? takeProfit - entry : entry - takeProfit; if (riskDistance <= 0 || rewardDistance <= 0) { document.getElementById('risk_reward').textContent = 'Invalid'; return; } document.getElementById('risk_amount').textContent = (riskDistance * 100 * lotSize).toFixed(2); document.getElementById('potential_profit').textContent = (rewardDistance * 100 * lotSize).toFixed(2); document.getElementById('risk_reward').textContent = `1:${(rewardDistance / riskDistance).toFixed(2)}`; };
dateInput.addEventListener('change', updateWeekday); fields.forEach((field) => field.addEventListener('input', calculateTrade)); updateWeekday(); calculateTrade();
</script>
@endpush
