<?php

namespace App\Http\Controllers;

use App\Models\TradeReason;
use Illuminate\View\View;

class PriceActionSetupController extends Controller
{
    public function index(): View
    {
        $setups = TradeReason::query()->orderByDesc('is_active')->orderBy('name')->get();

        return view('price-action-setups.index', compact('setups'));
    }
}
