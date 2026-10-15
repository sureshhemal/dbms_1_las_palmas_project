<?php

namespace App\Patient\UseCases;

use App\Patient\Entities\Models\Patient;
use App\Patient\IO\Http\DTOs\PatientData;

final class UpdatePatient
{
    public function __construct(private EnsurePrimaryPhysicianIsEligible $ensurePrimaryPhysicianIsEligible) {}

    public function execute(Patient $patient, PatientData $data): Patient
    {
        $this->ensurePrimaryPhysicianIsEligible->execute($data, $patient);

        $patient->update([
            'patientID' => $data->patientID ?? $patient->patientID,
            'name' => $data->name,
            'ssn' => $data->ssn,
            'address' => $data->address,
            'dob' => $data->dob?->toDateString(),
            'phone' => $data->phone,
            'insuranceNumber' => $data->insuranceNumber,
            'primary_physician_id' => $data->primaryPhysicianId,
        ]);

        return $patient->load('primaryPhysician:id,physicianID,name,deleted_at');
    }
}
