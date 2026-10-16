<?php

namespace App\Clinical\IO\Http\Controllers;

use App\Clinical\UseCases\ListPatientPerformedProcedures;
use App\Patient\Entities\Models\Patient;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PatientPerformedProceduresController
{
    public function index(Patient $patient, Request $request, ListPatientPerformedProcedures $listPatientPerformedProcedures): JsonResponse
    {
        return response()->json($listPatientPerformedProcedures->execute(
            patient: $patient,
            perPage: $request->integer('per_page', 10),
            page: $request->integer('page', 1),
        ));
    }
}
