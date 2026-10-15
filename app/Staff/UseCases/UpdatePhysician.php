<?php

namespace App\Staff\UseCases;

use App\Staff\Entities\Models\Physician;
use App\Staff\IO\Http\DTOs\PhysicianData;

final class UpdatePhysician
{
    public function execute(Physician $physician, PhysicianData $data): Physician
    {
        $physician->update([
            'physicianID' => $data->physicianID ?? $physician->physicianID,
            'name' => $data->name,
            'position' => $data->position,
            'ssn' => $data->ssn,
        ]);

        return $physician;
    }
}
