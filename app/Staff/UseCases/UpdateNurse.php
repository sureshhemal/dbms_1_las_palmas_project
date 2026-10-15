<?php

namespace App\Staff\UseCases;

use App\Staff\Entities\Models\Nurse;
use App\Staff\IO\Http\DTOs\NurseData;

final class UpdateNurse
{
    public function execute(Nurse $nurse, NurseData $data): Nurse
    {
        $nurse->update([
            'nurseID' => $data->nurseID ?? $nurse->nurseID,
            'name' => $data->name,
            'position' => $data->position,
            'ssn' => $data->ssn,
        ]);

        return $nurse;
    }
}
