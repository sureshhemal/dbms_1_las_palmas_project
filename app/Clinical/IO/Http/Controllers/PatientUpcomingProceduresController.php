<?php

namespace App\Clinical\IO\Http\Controllers;

use App\Clinical\UseCases\ListPatientUpcomingProcedures;
use App\Patient\Entities\Models\Patient;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PatientUpcomingProceduresController
{
    public function index(Patient $patient, Request $request, ListPatientUpcomingProcedures $listPatientUpcomingProcedures): JsonResponse
    {
        return response()->json($listPatientUpcomingProcedures->execute(
            patient: $patient,
            perPage: $request->integer('per_page', 10),
            page: $request->integer('page', 1),
        ));
    }
}
