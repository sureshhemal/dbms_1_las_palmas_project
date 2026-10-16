<?php

namespace App\Clinical\IO\Http\Controllers;

use App\Clinical\UseCases\ListPatientPrescriptions;
use App\Patient\Entities\Models\Patient;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PatientPrescriptionsController
{
    public function index(Patient $patient, Request $request, ListPatientPrescriptions $listPatientPrescriptions): JsonResponse
    {
        return response()->json($listPatientPrescriptions->execute(
            patient: $patient,
            perPage: $request->integer('per_page', 10),
            page: $request->integer('page', 1),
        ));
    }
}
