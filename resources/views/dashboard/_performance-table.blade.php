<div class="overflow-x-auto rounded-2xl border border-white/10 bg-slate-900/70">
    <table class="min-w-full text-left text-sm">
        <thead class="border-b border-white/10 bg-white/5 text-xs uppercase tracking-wider text-slate-400">
            <tr><th class="px-4 py-3">{{ $label }}</th><th class="px-4 py-3">Trades</th><th class="px-4 py-3">Wins</th><th class="px-4 py-3">Losses</th><th class="px-4 py-3">Win rate</th><th class="px-4 py-3">Net P/L</th><th class="px-4 py-3">Avg R:R</th>@if($showProfitFactor ?? false)<th class="px-4 py-3">Profit factor</th>@endif</tr>
        </thead>
        <tbody class="divide-y divide-white/10">
            @forelse($rows as $row)
                <tr class="hover:bg-white/[0.03]"><td class="px-4 py-3 font-medium text-white">{{ $row['name'] }}</td><td class="px-4 py-3 text-slate-300">{{ $row['trades'] }}</td><td class="px-4 py-3 text-emerald-300">{{ $row['wins'] }}</td><td class="px-4 py-3 text-rose-300">{{ $row['losses'] }}</td><td class="px-4 py-3 text-slate-300">{{ $row['win_rate'] === null ? '—' : number_format($row['win_rate'], 1).'%' }}</td><td class="px-4 py-3 font-medium {{ $row['net_profit_loss'] > 0 ? 'text-emerald-300' : ($row['net_profit_loss'] < 0 ? 'text-rose-300' : 'text-slate-300') }}">${{ number_format($row['net_profit_loss'], 2) }}</td><td class="px-4 py-3 text-slate-300">{{ $row['average_risk_reward'] === null ? '—' : '1:'.number_format($row['average_risk_reward'], 2) }}</td>@if($showProfitFactor ?? false)<td class="px-4 py-3 text-slate-300">{{ $row['profit_factor'] === null ? '—' : number_format($row['profit_factor'], 2) }}</td>@endif</tr>
            @empty
                <tr><td colspan="{{ ($showProfitFactor ?? false) ? 8 : 7 }}" class="px-4 py-10 text-center text-slate-400">No performance data for the selected filters.</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
