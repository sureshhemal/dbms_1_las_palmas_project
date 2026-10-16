<?php

namespace App\Clinical\IO\Http\Controllers;

use App\Clinical\UseCases\ShowClinicalSummary;
use App\Patient\Entities\Models\Patient;
use Illuminate\Http\JsonResponse;

class PatientSummaryController
{
    public function show(Patient $patient, ShowClinicalSummary $showClinicalSummary): JsonResponse
    {
        return response()->json($showClinicalSummary->execute($patient));
    }
}
