<?php

use App\Livewire\Kitchen\KitchenDisplay;
use App\Livewire\Pos\PosTerminal;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect('/admin');
});

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/pos', PosTerminal::class)->name('pos');
    Route::get('/kds', KitchenDisplay::class)->name('kds');
});
