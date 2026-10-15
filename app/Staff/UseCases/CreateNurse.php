<?php

namespace App\Staff\UseCases;

use App\Staff\Entities\Models\Nurse;
use App\Staff\IO\Http\DTOs\NurseData;

final class CreateNurse
{
    public function execute(NurseData $data): Nurse
    {
        return Nurse::query()->create(array_filter([
            'nurseID' => $data->nurseID,
            'name' => $data->name,
            'position' => $data->position,
            'ssn' => $data->ssn,
        ], fn (mixed $value): bool => $value !== null));
    }
}
