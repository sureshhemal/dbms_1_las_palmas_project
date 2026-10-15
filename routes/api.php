<?php

use App\Hospital\IO\Http\Controllers\DepartmentHeadCandidatesController;
use App\Hospital\IO\Http\Controllers\DepartmentPhysiciansController;
use App\Hospital\IO\Http\Controllers\DepartmentsController;
use App\Staff\IO\Http\Controllers\NursesController;
use App\Staff\IO\Http\Controllers\OnCallNurseCandidatesController;
use App\Staff\IO\Http\Controllers\OnCallsController;
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
    Route::get('departments/{department}', [DepartmentsController::class, 'show'])->name('api.departments.show');
    Route::put('departments/{department}', [DepartmentsController::class, 'update'])->name('api.departments.update');
    Route::get('departments/{department}/physicians', [DepartmentPhysiciansController::class, 'index'])->name('api.departments.physicians.index');
    Route::get('department-head-candidates', [DepartmentHeadCandidatesController::class, 'index'])->name('api.department-head-candidates.index');

    Route::get('nurses', [NursesController::class, 'index'])->name('api.nurses.index');
    Route::post('nurses', [NursesController::class, 'store'])->name('api.nurses.store');
    Route::put('nurses/{nurse}', [NursesController::class, 'update'])->name('api.nurses.update');
    Route::delete('nurses/{nurse}', [NursesController::class, 'destroy'])->name('api.nurses.destroy');

    Route::get('on-calls', [OnCallsController::class, 'index'])->name('api.on-calls.index');
    Route::post('on-calls', [OnCallsController::class, 'store'])->name('api.on-calls.store');
    Route::put('on-calls/{onCall}', [OnCallsController::class, 'update'])->name('api.on-calls.update');
    Route::delete('on-calls/{onCall}', [OnCallsController::class, 'destroy'])->name('api.on-calls.destroy');
    Route::get('on-call-nurse-candidates', [OnCallNurseCandidatesController::class, 'index'])->name('api.on-call-nurse-candidates.index');
});
