<?php

namespace App\Clinical\IO\Http\Controllers;

use App\Clinical\UseCases\ListPatientMedications;
use App\Patient\Entities\Models\Patient;
use Illuminate\Http\JsonResponse;

class PatientMedicationsController
{
    public function index(Patient $patient, ListPatientMedications $listPatientMedications): JsonResponse
    {
        return response()->json($listPatientMedications->execute($patient));
    }
}
