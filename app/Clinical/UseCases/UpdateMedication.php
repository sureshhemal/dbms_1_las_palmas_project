<?php

namespace App\Clinical\UseCases;

use App\Clinical\Entities\Models\Medication;
use App\Clinical\IO\Http\DTOs\MedicationData;

final class UpdateMedication
{
    public function execute(Medication $medication, MedicationData $data): Medication
    {
        $medication->update([
            'medID' => $data->medID ?? $medication->medID,
            'name' => $data->name,
        ]);

        return $medication;
    }
}
