<?php

use App\Hospital\IO\Http\Controllers\DepartmentPageController;
use App\Patient\IO\Http\Controllers\PatientPageController;
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
    Route::inertia('patients', 'Patients')->name('patients');
    Route::get('patients/{patient}', [PatientPageController::class, 'show'])->name('patients.show');
});

require __DIR__.'/settings.php';
