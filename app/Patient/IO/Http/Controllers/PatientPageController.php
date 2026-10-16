<?php

namespace App\Patient\IO\Http\Controllers;

use App\Patient\Entities\Models\Patient;
use Inertia\Inertia;
use Inertia\Response;

class PatientPageController
{
    public function show(Patient $patient): Response
    {
        return Inertia::render('Patient', [
            'patientId' => $patient->id,
        ]);
    }
}
