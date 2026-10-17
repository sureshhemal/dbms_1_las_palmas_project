<?php

use App\Clinical\IO\Http\Controllers\AppointmentsController;
use App\Clinical\IO\Http\Controllers\DashboardController;
use App\Clinical\IO\Http\Controllers\MedicationsController;
use App\Clinical\IO\Http\Controllers\PatientAppointmentsController;
use App\Clinical\IO\Http\Controllers\PatientMedicationsController;
use App\Clinical\IO\Http\Controllers\PatientPerformedProceduresController;
use App\Clinical\IO\Http\Controllers\PatientPrescriptionsController;
use App\Clinical\IO\Http\Controllers\PatientSummaryController;
use App\Clinical\IO\Http\Controllers\PatientUpcomingProceduresController;
use App\Clinical\IO\Http\Controllers\PerformedProceduresController;
use App\Clinical\IO\Http\Controllers\PerformedProcedureYearsController;
use App\Clinical\IO\Http\Controllers\PhysicianAppointmentsController;
use App\Clinical\IO\Http\Controllers\PhysicianPerformedProceduresController;
use App\Clinical\IO\Http\Controllers\PhysicianPrescriptionsController;
use App\Clinical\IO\Http\Controllers\PhysicianProceduresController;
use App\Clinical\IO\Http\Controllers\PhysicianSummaryController;
use App\Clinical\IO\Http\Controllers\PhysicianUpcomingProceduresController;
use App\Clinical\IO\Http\Controllers\PrescriberCandidatesController;
use App\Clinical\IO\Http\Controllers\PrescriptionsController;
use App\Clinical\IO\Http\Controllers\ProcedureAverageCostController;
use App\Clinical\IO\Http\Controllers\ProceduresController;
use App\Clinical\IO\Http\Controllers\RoomAvailabilityController;
use App\Clinical\IO\Http\Controllers\StaysController;
use App\Hospital\IO\Http\Controllers\DepartmentHeadCandidatesController;
use App\Hospital\IO\Http\Controllers\DepartmentPhysiciansController;
use App\Hospital\IO\Http\Controllers\DepartmentsController;
use App\Hospital\IO\Http\Controllers\RoomsController;
use App\Patient\IO\Http\Controllers\PatientsController;
use App\Patient\IO\Http\Controllers\PrimaryPhysicianCandidatesController;
use App\Staff\IO\Http\Controllers\NursesController;
use App\Staff\IO\Http\Controllers\OnCallNurseCandidatesController;
use App\Staff\IO\Http\Controllers\OnCallsController;
use App\Staff\IO\Http\Controllers\PhysiciansController;
use App\Staff\IO\Http\Controllers\StaffController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth')->group(function () {
    Route::get('staff', [StaffController::class, 'index'])->name('api.staff.index');

    Route::get('physicians', [PhysiciansController::class, 'index'])->name('api.physicians.index');
    Route::get('physicians/{physician}', [PhysiciansController::class, 'show'])->name('api.physicians.show');
    Route::get('physicians/{physician}/summary', [PhysicianSummaryController::class, 'show'])->name('api.physicians.summary.show');
    Route::get('physicians/{physician}/procedures', [PhysicianProceduresController::class, 'index'])->name('api.physicians.procedures.index');
    Route::get('physicians/{physician}/upcoming-procedures', [PhysicianUpcomingProceduresController::class, 'index'])->name('api.physicians.upcoming-procedures.index');
    Route::get('physicians/{physician}/performed-procedures', [PhysicianPerformedProceduresController::class, 'index'])->name('api.physicians.performed-procedures.index');
    Route::get('physicians/{physician}/appointments', [PhysicianAppointmentsController::class, 'index'])->name('api.physicians.appointments.index');
    Route::get('physicians/{physician}/prescriptions', [PhysicianPrescriptionsController::class, 'index'])->name('api.physicians.prescriptions.index');
    Route::post('physicians', [PhysiciansController::class, 'store'])->name('api.physicians.store');
    Route::put('physicians/{physician}', [PhysiciansController::class, 'update'])->name('api.physicians.update');
    Route::delete('physicians/{physician}', [PhysiciansController::class, 'destroy'])->name('api.physicians.destroy');

    Route::get('departments', [DepartmentsController::class, 'index'])->name('api.departments.index');
    Route::get('departments/{department}', [DepartmentsController::class, 'show'])->name('api.departments.show');
    Route::put('departments/{department}', [DepartmentsController::class, 'update'])->name('api.departments.update');
    Route::get('departments/{department}/physicians', [DepartmentPhysiciansController::class, 'index'])->name('api.departments.physicians.index');
    Route::get('department-head-candidates', [DepartmentHeadCandidatesController::class, 'index'])->name('api.department-head-candidates.index');

    Route::get('rooms', [RoomsController::class, 'index'])->name('api.rooms.index');
    Route::post('rooms', [RoomsController::class, 'store'])->name('api.rooms.store');
    Route::put('rooms/{room}', [RoomsController::class, 'update'])->name('api.rooms.update');
    Route::delete('rooms/{room}', [RoomsController::class, 'destroy'])->name('api.rooms.destroy');

    Route::get('medications', [MedicationsController::class, 'index'])->name('api.medications.index');
    Route::post('medications', [MedicationsController::class, 'store'])->name('api.medications.store');
    Route::put('medications/{medication}', [MedicationsController::class, 'update'])->name('api.medications.update');
    Route::delete('medications/{medication}', [MedicationsController::class, 'destroy'])->name('api.medications.destroy');

    Route::get('procedures', [ProceduresController::class, 'index'])->name('api.procedures.index');
    Route::get('procedures/average-cost', [ProcedureAverageCostController::class, 'show'])->name('api.procedures.average-cost.show');
    Route::post('procedures', [ProceduresController::class, 'store'])->name('api.procedures.store');
    Route::put('procedures/{procedure}', [ProceduresController::class, 'update'])->name('api.procedures.update');
    Route::delete('procedures/{procedure}', [ProceduresController::class, 'destroy'])->name('api.procedures.destroy');

    Route::get('dashboard', [DashboardController::class, 'show'])->name('api.dashboard.show');

    Route::get('appointments', [AppointmentsController::class, 'index'])->name('api.appointments.index');
    Route::get('appointments/years', [AppointmentsController::class, 'years'])->name('api.appointments.years');
    Route::post('appointments', [AppointmentsController::class, 'store'])->name('api.appointments.store');
    Route::put('appointments/{appointment}', [AppointmentsController::class, 'update'])->name('api.appointments.update');
    Route::delete('appointments/{appointment}', [AppointmentsController::class, 'destroy'])->name('api.appointments.destroy');

    Route::get('prescriptions', [PrescriptionsController::class, 'index'])->name('api.prescriptions.index');
    Route::get('prescriptions/years', [PrescriptionsController::class, 'years'])->name('api.prescriptions.years');
    Route::post('prescriptions', [PrescriptionsController::class, 'store'])->name('api.prescriptions.store');

    Route::get('stays', [StaysController::class, 'index'])->name('api.stays.index');
    Route::patch('stays/{stay}/discharge', [StaysController::class, 'discharge'])->name('api.stays.discharge');
    Route::patch('stays/{stay}/extend', [StaysController::class, 'extend'])->name('api.stays.extend');
    Route::post('stays/{stay}/procedures', [StaysController::class, 'recordProcedure'])->name('api.stays.procedures.store');
    Route::get('performed-procedures', [PerformedProceduresController::class, 'index'])->name('api.performed-procedures.index');
    Route::get('performed-procedures/years', [PerformedProcedureYearsController::class, 'index'])->name('api.performed-procedures.years');
    Route::post('performed-procedures', [PerformedProceduresController::class, 'store'])->name('api.performed-procedures.store');
    Route::get('room-availability', [RoomAvailabilityController::class, 'index'])->name('api.room-availability.index');

    Route::get('prescriber-candidates', [PrescriberCandidatesController::class, 'index'])->name('api.prescriber-candidates.index');

    Route::get('patients', [PatientsController::class, 'index'])->name('api.patients.index');
    Route::get('patients/{patient}', [PatientsController::class, 'show'])->name('api.patients.show');
    Route::get('patients/{patient}/summary', [PatientSummaryController::class, 'show'])->name('api.patients.summary.show');
    Route::get('patients/{patient}/performed-procedures', [PatientPerformedProceduresController::class, 'index'])->name('api.patients.performed-procedures.index');
    Route::get('patients/{patient}/upcoming-procedures', [PatientUpcomingProceduresController::class, 'index'])->name('api.patients.upcoming-procedures.index');
    Route::get('patients/{patient}/medications', [PatientMedicationsController::class, 'index'])->name('api.patients.medications.index');
    Route::get('patients/{patient}/appointments', [PatientAppointmentsController::class, 'index'])->name('api.patients.appointments.index');
    Route::get('patients/{patient}/prescriptions', [PatientPrescriptionsController::class, 'index'])->name('api.patients.prescriptions.index');
    Route::post('patients', [PatientsController::class, 'store'])->name('api.patients.store');
    Route::put('patients/{patient}', [PatientsController::class, 'update'])->name('api.patients.update');
    Route::delete('patients/{patient}', [PatientsController::class, 'destroy'])->name('api.patients.destroy');
    Route::get('primary-physician-candidates', [PrimaryPhysicianCandidatesController::class, 'index'])->name('api.primary-physician-candidates.index');

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
