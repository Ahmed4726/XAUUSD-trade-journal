<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'XAUUSD Trading Journal')</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-slate-950 font-sans text-slate-100 antialiased">
    <div class="min-h-screen bg-[radial-gradient(ellipse_at_top,_var(--tw-gradient-stops))] from-slate-800 via-slate-950 to-slate-950">
        <header class="border-b border-white/10 bg-slate-950/70 backdrop-blur">
            <div class="mx-auto flex max-w-7xl items-center justify-between gap-6 px-4 py-4 sm:px-6 lg:px-8">
                <a href="{{ route('dashboard') }}" class="flex items-center gap-3">
                    <span class="grid size-10 place-items-center rounded-xl bg-amber-400 font-black text-slate-950">X</span>
                    <span>
                        <span class="block text-sm font-semibold tracking-wide text-white">XAUUSD</span>
                        <span class="block text-xs text-slate-400">Trading Journal</span>
                    </span>
                </a>
                <nav class="flex items-center gap-2 text-sm font-medium">
                    <a href="{{ route('dashboard') }}" class="rounded-lg px-3 py-2 text-slate-300 transition hover:bg-white/10 hover:text-white">Dashboard</a>
                    <a href="{{ route('trades.index') }}" class="rounded-lg px-3 py-2 text-slate-300 transition hover:bg-white/10 hover:text-white">Trades</a>
                    <a href="{{ route('price-action-setups.index') }}" class="rounded-lg px-3 py-2 text-slate-300 transition hover:bg-white/10 hover:text-white">Price Action Setups</a>
                    <a href="{{ route('trade-mistakes.index') }}" class="rounded-lg px-3 py-2 text-slate-300 transition hover:bg-white/10 hover:text-white">Mistakes</a>
                    <a href="{{ route('trades.create') }}" class="rounded-lg bg-amber-400 px-3 py-2 text-slate-950 transition hover:bg-amber-300">Add trade</a>
                </nav>
            </div>
        </header>

        <main class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
            @if(session('success'))
                <div class="mb-6 rounded-xl border border-emerald-400/30 bg-emerald-400/10 px-4 py-3 text-sm text-emerald-200">
                    {{ session('success') }}
                </div>
            @endif

            @if($errors->any())
                <div class="mb-6 rounded-xl border border-rose-400/30 bg-rose-400/10 px-4 py-3 text-sm text-rose-200">
                    <p class="font-semibold">Please correct the highlighted fields.</p>
                </div>
            @endif

            @yield('content')
        </main>
    </div>

    @stack('scripts')
</body>
</html>
