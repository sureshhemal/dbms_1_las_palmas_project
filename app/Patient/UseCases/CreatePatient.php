<?php

namespace App\Patient\UseCases;

use App\Patient\Entities\Models\Patient;
use App\Patient\IO\Http\DTOs\PatientData;

final class CreatePatient
{
    public function __construct(private EnsurePrimaryPhysicianIsEligible $ensurePrimaryPhysicianIsEligible) {}

    public function execute(PatientData $data): Patient
    {
        $this->ensurePrimaryPhysicianIsEligible->execute($data, null);

        $patient = new Patient([
            'name' => $data->name,
            'ssn' => $data->ssn,
            'address' => $data->address,
            'dob' => $data->dob?->toDateString(),
            'phone' => $data->phone,
            'insuranceNumber' => $data->insuranceNumber,
            'primary_physician_id' => $data->primaryPhysicianId,
        ]);

        if ($data->patientID !== null) {
            $patient->patientID = $data->patientID;
        }

        $patient->save();

        return $patient->load('primaryPhysician:id,physicianID,name,deleted_at');
    }
}
