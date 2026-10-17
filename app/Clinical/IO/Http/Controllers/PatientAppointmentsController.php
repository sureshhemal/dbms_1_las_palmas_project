<?php

namespace App\Clinical\IO\Http\Controllers;

use App\Clinical\UseCases\ListAppointmentsOf;
use App\Patient\Entities\Models\Patient;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PatientAppointmentsController
{
    public function index(Patient $patient, Request $request, ListAppointmentsOf $listAppointmentsOf): JsonResponse
    {
        return response()->json($listAppointmentsOf->execute(
            subject: $patient,
            perPage: $request->integer('per_page', 10),
            page: $request->integer('page', 1),
        ));
    }
}
