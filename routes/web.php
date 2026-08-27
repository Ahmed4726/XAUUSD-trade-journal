<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\PriceActionSetupController;
use App\Http\Controllers\TradeController;
use App\Http\Controllers\TradeMistakeController;
use App\Http\Controllers\TradeScreenshotController;
use Illuminate\Support\Facades\Route;

Route::get('/', DashboardController::class)->name('dashboard');
Route::get('dashboard', DashboardController::class)->name('dashboard.index');
Route::get('price-action-setups', [PriceActionSetupController::class, 'index'])->name('price-action-setups.index');
Route::resource('trades', TradeController::class);
Route::resource('trade-mistakes', TradeMistakeController::class);
Route::post('trades/{trade}/screenshots', [TradeScreenshotController::class, 'store'])->name('trades.screenshots.store');
Route::delete('trades/{trade}/screenshots/{tradeImage}', [TradeScreenshotController::class, 'destroy'])->name('trades.screenshots.destroy');
