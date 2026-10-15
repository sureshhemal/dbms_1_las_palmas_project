<?php

use App\Hospital\IO\Http\Controllers\DepartmentHeadCandidatesController;
use App\Hospital\IO\Http\Controllers\DepartmentsController;
use App\Staff\IO\Http\Controllers\NursesController;
use App\Staff\IO\Http\Controllers\PhysiciansController;
use App\Staff\IO\Http\Controllers\StaffController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth')->group(function () {
    Route::get('staff', [StaffController::class, 'index'])->name('api.staff.index');

    Route::get('physicians', [PhysiciansController::class, 'index'])->name('api.physicians.index');
    Route::post('physicians', [PhysiciansController::class, 'store'])->name('api.physicians.store');
    Route::put('physicians/{physician}', [PhysiciansController::class, 'update'])->name('api.physicians.update');
    Route::delete('physicians/{physician}', [PhysiciansController::class, 'destroy'])->name('api.physicians.destroy');

    Route::get('departments', [DepartmentsController::class, 'index'])->name('api.departments.index');
    Route::put('departments/{department}', [DepartmentsController::class, 'update'])->name('api.departments.update');
    Route::get('department-head-candidates', [DepartmentHeadCandidatesController::class, 'index'])->name('api.department-head-candidates.index');

    Route::get('nurses', [NursesController::class, 'index'])->name('api.nurses.index');
    Route::post('nurses', [NursesController::class, 'store'])->name('api.nurses.store');
    Route::put('nurses/{nurse}', [NursesController::class, 'update'])->name('api.nurses.update');
    Route::delete('nurses/{nurse}', [NursesController::class, 'destroy'])->name('api.nurses.destroy');
});
