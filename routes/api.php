<?php

use App\Staff\IO\Http\Controllers\NursesController;
use App\Staff\IO\Http\Controllers\PhysiciansController;
use App\Staff\IO\Http\Controllers\StaffController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth')->group(function () {
    Route::get('staff', [StaffController::class, 'index'])->name('api.staff.index');

    Route::get('physicians', [PhysiciansController::class, 'index'])->name('api.physicians.index');
    Route::post('physicians', [PhysiciansController::class, 'store'])->name('api.physicians.store');
    Route::put('physicians/{physician}', [PhysiciansController::class, 'update'])->name('api.physicians.update');

    Route::get('nurses', [NursesController::class, 'index'])->name('api.nurses.index');
    Route::post('nurses', [NursesController::class, 'store'])->name('api.nurses.store');
    Route::put('nurses/{nurse}', [NursesController::class, 'update'])->name('api.nurses.update');
});
