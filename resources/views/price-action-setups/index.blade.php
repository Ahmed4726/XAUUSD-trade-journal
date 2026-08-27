@extends('layouts.app')

@section('title', 'Price Action Setups | XAUUSD Trading Journal')

@section('content')
    <div class="mb-8"><p class="text-sm font-medium text-amber-300">XAUUSD price action</p><h1 class="mt-1 text-3xl font-bold text-white">Price Action Setups</h1><p class="mt-2 text-sm text-slate-400">The setups used to record why a trade was entered.</p></div>
    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">@forelse($setups as $setup)<article class="rounded-2xl border border-white/10 bg-slate-900/70 p-5"><div class="flex items-start justify-between gap-3"><h2 class="font-semibold text-white">{{ $setup->name }}</h2><span class="badge {{ $setup->is_active ? 'badge-win' : 'badge-breakeven' }}">{{ $setup->is_active ? 'ACTIVE' : 'INACTIVE' }}</span></div><p class="mt-3 text-sm leading-6 text-slate-400">{{ $setup->description ?: 'No description recorded.' }}</p></article>@empty<div class="rounded-2xl border border-dashed border-white/15 bg-slate-900/50 px-6 py-14 text-center text-slate-400 sm:col-span-2 lg:col-span-3">No price action setups recorded.</div>@endforelse</div>
@endsection
