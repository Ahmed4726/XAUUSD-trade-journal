@extends('layouts.app')

@section('title', 'Trades | XAUUSD Trading Journal')

@section('content')
    <div class="mb-8 flex flex-col justify-between gap-4 sm:flex-row sm:items-end">
        <div>
            <p class="text-sm font-medium text-amber-300">XAUUSD only</p>
            <h1 class="mt-1 text-3xl font-bold tracking-tight text-white">Trade journal</h1>
            <p class="mt-2 text-sm text-slate-400">Review your price action trades and execution.</p>
        </div>
        <a href="{{ route('trades.create') }}" class="inline-flex items-center justify-center rounded-lg bg-amber-400 px-4 py-2.5 text-sm font-semibold text-slate-950 hover:bg-amber-300">Add trade</a>
    </div>

    <form method="GET" class="mb-6 grid gap-3 rounded-2xl border border-white/10 bg-slate-900/70 p-4 sm:grid-cols-2 lg:grid-cols-4 xl:grid-cols-5">
        <input type="date" name="date" value="{{ request('date') }}" class="journal-input" aria-label="Exact date">
        <input type="date" name="date_from" value="{{ request('date_from') }}" class="journal-input" aria-label="Date from">
        <input type="date" name="date_to" value="{{ request('date_to') }}" class="journal-input" aria-label="Date to">
        <select name="direction" class="journal-input"><option value="">All directions</option><option value="buy" @selected(request('direction') === 'buy')>BUY</option><option value="sell" @selected(request('direction') === 'sell')>SELL</option></select>
        <select name="timeframe" class="journal-input"><option value="">All timeframes</option>@foreach(['15m', '30m', '1h', '4h'] as $timeframe)<option value="{{ $timeframe }}" @selected(request('timeframe') === $timeframe)>{{ $timeframe }}</option>@endforeach</select>
        <select name="trade_reason_id" class="journal-input"><option value="">All setups</option>@foreach($reasons as $reason)<option value="{{ $reason->id }}" @selected((string) request('trade_reason_id') === (string) $reason->id)>{{ $reason->name }}</option>@endforeach</select>
        <select name="entry_type_id" class="journal-input"><option value="">All entry types</option>@foreach($entryTypes as $entryType)<option value="{{ $entryType->id }}" @selected((string) request('entry_type_id') === (string) $entryType->id)>{{ $entryType->name }}</option>@endforeach</select>
        <select name="market_condition" class="journal-input"><option value="">All market conditions</option>@foreach(['trending', 'ranging', 'consolidating', 'volatile', 'choppy', 'clean'] as $condition)<option value="{{ $condition }}" @selected(request('market_condition') === $condition)>{{ ucfirst($condition) }}</option>@endforeach</select>
        <select name="status" class="journal-input"><option value="">All results</option>@foreach(['open', 'win', 'loss', 'breakeven'] as $status)<option value="{{ $status }}" @selected(request('status') === $status)>{{ ucfirst($status) }}</option>@endforeach</select>
        <div class="flex gap-2"><button class="flex-1 rounded-lg bg-white px-3 py-2 text-sm font-semibold text-slate-950 hover:bg-slate-200">Filter</button><a href="{{ route('trades.index') }}" class="rounded-lg border border-white/15 px-3 py-2 text-sm text-slate-300 hover:bg-white/10">Clear</a></div>
    </form>

    <div class="overflow-hidden rounded-2xl border border-white/10 bg-slate-900/70 shadow-2xl shadow-black/20">
        <div class="overflow-x-auto">
            <table class="min-w-full text-left text-sm">
                <thead class="border-b border-white/10 bg-white/5 text-xs uppercase tracking-wider text-slate-400"><tr><th class="px-4 py-3">Date</th><th class="px-4 py-3">Direction</th><th class="px-4 py-3">Setup</th><th class="px-4 py-3">Levels</th><th class="px-4 py-3">Lot</th><th class="px-4 py-3">R:R</th><th class="px-4 py-3">P/L</th><th class="px-4 py-3">Result</th><th class="px-4 py-3"><span class="sr-only">Actions</span></th></tr></thead>
                <tbody class="divide-y divide-white/10">
                    @forelse($trades as $trade)
                        <tr class="hover:bg-white/[0.03]">
                            <td class="px-4 py-4"><div class="font-medium text-white">{{ $trade->trade_date->format('d M Y') }}</div><div class="text-xs text-slate-500">{{ $trade->weekday }} · {{ $trade->timeframe }}</div></td>
                            <td class="px-4 py-4"><span class="badge {{ $trade->direction === 'buy' ? 'badge-buy' : 'badge-sell' }}">{{ strtoupper($trade->direction) }}</span></td>
                            <td class="px-4 py-4 text-slate-300"><div>{{ $trade->reason?->name ?? '—' }}</div><div class="text-xs text-slate-500">{{ $trade->entryType?->name ?? '—' }}</div></td>
                            <td class="px-4 py-4 font-mono text-xs leading-5 text-slate-300"><div>E {{ number_format((float) $trade->entry_price, 2) }}</div><div>SL {{ number_format((float) $trade->stop_loss, 2) }} · TP {{ number_format((float) $trade->take_profit, 2) }}</div></td>
                            <td class="px-4 py-4 text-slate-300">{{ number_format((float) $trade->lot_size, 2) }}</td>
                            <td class="px-4 py-4 text-slate-300">1:{{ number_format((float) $trade->risk_reward, 2) }}</td>
                            <td class="px-4 py-4 font-medium {{ $trade->actual_profit_loss > 0 ? 'text-emerald-300' : ($trade->actual_profit_loss < 0 ? 'text-rose-300' : 'text-slate-400') }}">{{ $trade->actual_profit_loss === null ? '—' : '$'.number_format((float) $trade->actual_profit_loss, 2) }}</td>
                            <td class="px-4 py-4"><span class="badge badge-{{ $trade->status }}">{{ strtoupper($trade->status) }}</span></td>
                            <td class="px-4 py-4 text-right"><a href="{{ route('trades.show', $trade) }}" class="text-sm font-medium text-amber-300 hover:text-amber-200">View</a></td>
                        </tr>
                    @empty
                        <tr><td colspan="9" class="px-4 py-14 text-center text-slate-400">No trades match these filters. <a href="{{ route('trades.create') }}" class="text-amber-300 hover:underline">Add your first trade.</a></td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($trades->hasPages())<div class="border-t border-white/10 px-4 py-4">{{ $trades->links() }}</div>@endif
    </div>
@endsection
