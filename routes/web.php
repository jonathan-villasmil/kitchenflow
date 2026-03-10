<?php

use App\Livewire\Kitchen\KitchenDisplay;
use App\Livewire\Pos\PosTerminal;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    if (auth()->check()) {
        $user = auth()->user();
        if ($user->hasRole('camarero')) return redirect()->route('pos');
        if ($user->hasRole('cocinero')) return redirect()->route('kds');
        return redirect('/admin');
    }
    return view('landing');
});

Route::get('/login', function () {
    return redirect('/admin/login');
})->name('login');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/pos', PosTerminal::class)->name('pos');
    Route::get('/kds', KitchenDisplay::class)->name('kds');
    
    // Z-Report PDF Route
    Route::get('/pos/z-report/{register}', [\App\Http\Controllers\ZReportController::class, 'download'])
        ->name('pos.z-report');
});
