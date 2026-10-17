<?php

namespace App\Clinical\IO\Http\Controllers;

use App\Clinical\Entities\Models\Appointment;
use App\Clinical\IO\Http\Requests\AppointmentRequest;
use App\Clinical\IO\Http\Requests\ListAppointmentsRequest;
use App\Clinical\UseCases\CancelAppointment;
use App\Clinical\UseCases\CreateAppointment;
use App\Clinical\UseCases\ListAppointments;
use App\Clinical\UseCases\ListAppointmentYears;
use App\Clinical\UseCases\UpdateAppointment;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;

class AppointmentsController
{
    public function index(ListAppointmentsRequest $request, ListAppointments $listAppointments): JsonResponse
    {
        return response()->json($listAppointments->execute(
            perPage: $request->integer('per_page', 20),
            page: $request->integer('page', 1),
            filters: $request->getFilters(),
        ));
    }

    public function years(ListAppointmentYears $listAppointmentYears): JsonResponse
    {
        return response()->json($listAppointmentYears->execute());
    }

    public function store(AppointmentRequest $request, CreateAppointment $createAppointment): JsonResponse
    {
        return response()->json($createAppointment->execute($request->getData()), 201);
    }

    public function update(Appointment $appointment, AppointmentRequest $request, UpdateAppointment $updateAppointment): JsonResponse
    {
        return response()->json($updateAppointment->execute($appointment, $request->getData()));
    }

    public function destroy(Appointment $appointment, CancelAppointment $cancelAppointment): Response
    {
        $cancelAppointment->execute($appointment);

        return response()->noContent();
    }
}
