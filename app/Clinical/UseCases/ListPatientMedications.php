<?php

namespace App\Clinical\UseCases;

use App\Clinical\Entities\MedicationShare;
use App\Clinical\Entities\Models\Prescription;
use App\Patient\Entities\Models\Patient;
use stdClass;

final class ListPatientMedications
{
    public function execute(Patient $patient): array
    {
        return Prescription::query()
            ->join('Medication', 'Medication.id', '=', 'Prescribes.medication_id')
            ->whereBelongsTo($patient)
            ->groupBy('Medication.id')
            ->select(['Medication.id', 'Medication.name', 'Medication.deleted_at'])
            ->selectRaw('count(*) as prescriptionsCount')
            ->selectRaw('round(100 * count(*) / (select count(*) from Prescribes where patient_id = ? and deleted_at is null), 1) as percentage', [$patient->id])
            ->orderByDesc('prescriptionsCount')
            ->orderBy('Medication.name')
            ->toBase()
            ->get()
            ->map(fn (stdClass $medication): MedicationShare => new MedicationShare(
                medicationId: $medication->id,
                name: $medication->name,
                isRemoved: $medication->deleted_at !== null,
                prescriptionsCount: (int) $medication->prescriptionsCount,
                percentage: (float) $medication->percentage,
            ))
            ->all();
    }
}
