<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreTradeMistakeRequest;
use App\Http\Requests\UpdateTradeMistakeRequest;
use App\Models\TradeMistake;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class TradeMistakeController extends Controller
{
    public function index(): View
    {
        $mistakes = TradeMistake::query()->withCount('trades')->orderByDesc('is_active')->orderBy('name')->paginate(20);

        return view('trade-mistakes.index', compact('mistakes'));
    }

    public function create(): View
    {
        return view('trade-mistakes.create');
    }

    public function store(StoreTradeMistakeRequest $request): RedirectResponse
    {
        TradeMistake::create($request->validated());

        return redirect()->route('trade-mistakes.index')->with('success', 'Trade mistake added successfully.');
    }

    public function show(TradeMistake $tradeMistake): View
    {
        $tradeMistake->loadCount('trades');

        return view('trade-mistakes.show', compact('tradeMistake'));
    }

    public function edit(TradeMistake $tradeMistake): View
    {
        return view('trade-mistakes.edit', compact('tradeMistake'));
    }

    public function update(UpdateTradeMistakeRequest $request, TradeMistake $tradeMistake): RedirectResponse
    {
        $tradeMistake->update($request->validated());

        return redirect()->route('trade-mistakes.show', $tradeMistake)->with('success', 'Trade mistake updated successfully.');
    }

    public function destroy(TradeMistake $tradeMistake): RedirectResponse
    {
        if ($tradeMistake->trades()->exists()) {
            $tradeMistake->update(['is_active' => false]);

            return redirect()->route('trade-mistakes.index')->with('success', 'This mistake is referenced by trades and was deactivated instead.');
        }

        $tradeMistake->delete();

        return redirect()->route('trade-mistakes.index')->with('success', 'Trade mistake deleted successfully.');
    }
}
