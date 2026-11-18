<?php

use App\Http\Controllers\CheckUpController;
use App\Http\Controllers\ComponentController;
use App\Http\Controllers\ComponentReplacementController;
use App\Http\Controllers\ExportController;
use App\Http\Controllers\HmLogController;
use App\Http\Controllers\MachineController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\StationController;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/dashboard', [HmLogController::class, 'index'])->name('dashboard');
    Route::get('/hm-input', [HmLogController::class, 'index'])->name('hm.input');
    Route::post('/hm-input', [HmLogController::class, 'store'])->name('hm.store');
    Route::get('/check-ups/export', [CheckUpController::class, 'export'])->name('check-ups.export');
    Route::resource('check-ups', CheckUpController::class)->only(['index', 'store']);
    Route::resource('components', ComponentController::class)->only([
        'index', 'store', 'edit', 'update', 'destroy',
    ]);
    Route::get('/components/{component}', [ComponentController::class, 'show'])->name('components.show');
    Route::get('/components/{component}/replacement', [ComponentReplacementController::class, 'create'])
        ->name('components.replacements.create');
    Route::post('/components/{component}/replacement', [ComponentReplacementController::class, 'store'])
        ->name('components.replacements.store');
    Route::get('/component-replacements/export', [ComponentReplacementController::class, 'export'])
        ->name('component-replacements.export');
    Route::get('/component-replacements', [ComponentReplacementController::class, 'index'])
        ->name('component-replacements.index');
    Route::get('/hm-reports', [ExportController::class, 'index'])->name('hm.reports');
    Route::get('/hm-export', [ExportController::class, 'monthly'])->name('hm.export');
    Route::get('/stations', [StationController::class, 'index'])->name('stations.index');
    Route::post('/stations', [StationController::class, 'store'])->name('stations.store');
    Route::get('/stations/{station}', [StationController::class, 'show'])->name('stations.show');
    Route::put('/stations/{station}', [StationController::class, 'update'])->name('stations.update');
    Route::delete('/stations/{station}', [StationController::class, 'destroy'])->name('stations.destroy');
    Route::post('/stations/{station}/machines', [MachineController::class, 'storeForStation'])
        ->name('stations.machines.store');
    Route::get('/machines', [MachineController::class, 'index'])->name('machines.index');
    Route::post('/machines', [MachineController::class, 'store'])->name('machines.store');
    Route::get('/machines/{machine}/edit', [MachineController::class, 'edit'])->name('machines.edit');
    Route::put('/machines/{machine}', [MachineController::class, 'update'])->name('machines.update');
    Route::delete('/machines/{machine}', [MachineController::class, 'destroy'])->name('machines.destroy');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
