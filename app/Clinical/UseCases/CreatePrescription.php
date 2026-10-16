<?php

namespace App\Clinical\UseCases;

use App\Clinical\Entities\Models\Prescription;
use App\Clinical\IO\Http\DTOs\PrescriptionData;

final class CreatePrescription
{
    public function __construct(private EnsurePrescriptionIsAllowed $ensurePrescriptionIsAllowed) {}

    public function execute(PrescriptionData $data): Prescription
    {
        $this->ensurePrescriptionIsAllowed->execute($data);

        $prescription = new Prescription([
            'patient_id' => $data->patientId,
            'medication_id' => $data->medicationId,
            'physician_id' => $data->physicianId,
            'prescribedDate' => $data->prescribedDate?->toDateString(),
            'dose' => $data->dose,
        ]);
        $prescription->save();

        return $prescription->load(ListPrescriptions::RELATIONS);
    }
}
