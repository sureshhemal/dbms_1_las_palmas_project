<?php

namespace App\Clinical\IO\Http\Controllers;

use App\Clinical\UseCases\ShowPatientSummary;
use App\Patient\Entities\Models\Patient;
use Illuminate\Http\JsonResponse;

class PatientSummaryController
{
    public function show(Patient $patient, ShowPatientSummary $showPatientSummary): JsonResponse
    {
        return response()->json($showPatientSummary->execute($patient));
    }
}
