<?php

namespace App\Staff\UseCases;

use App\Staff\Entities\Models\Physician;
use App\Staff\IO\Http\DTOs\PhysicianData;

final class CreatePhysician
{
    public function execute(PhysicianData $data): Physician
    {
        return Physician::query()->create(array_filter([
            'physicianID' => $data->physicianID,
            'name' => $data->name,
            'position' => $data->position,
            'ssn' => $data->ssn,
        ], fn (mixed $value): bool => $value !== null));
    }
}
