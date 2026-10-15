<?php

use App\Hospital\IO\Http\Controllers\DepartmentPageController;
use Illuminate\Support\Facades\Route;

Route::inertia('/', 'Welcome')->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::inertia('dashboard', 'Dashboard')->name('dashboard');
});

Route::middleware('auth')->group(function () {
    Route::inertia('staff', 'Staff')->name('staff');
    Route::inertia('hospital', 'Hospital')->name('hospital');
    Route::get('hospital/departments/{department}', [DepartmentPageController::class, 'show'])->name('hospital.departments.show');
    Route::inertia('clinical', 'Clinical')->name('clinical');
});

require __DIR__.'/settings.php';
