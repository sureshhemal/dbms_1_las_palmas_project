<?php

namespace App\Clinical\UseCases;

use App\Clinical\Entities\Models\Medication;
use App\Clinical\IO\Http\DTOs\MedicationData;

final class CreateMedication
{
    public function execute(MedicationData $data): Medication
    {
        $medication = new Medication(['name' => $data->name]);

        if ($data->medID !== null) {
            $medication->medID = $data->medID;
        }

        $medication->save();

        return $medication;
    }
}
