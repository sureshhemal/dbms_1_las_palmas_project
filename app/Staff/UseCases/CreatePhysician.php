<?php

namespace App\Staff\UseCases;

use App\Staff\Entities\Models\Physician;
use App\Staff\IO\Http\DTOs\PhysicianData;
use Illuminate\Support\Facades\DB;

final class CreatePhysician
{
    public function execute(PhysicianData $data): Physician
    {
        return DB::transaction(function () use ($data): Physician {
            $physician = new Physician(array_filter([
                'physicianID' => $data->physicianID,
                'name' => $data->name,
                'position' => $data->position,
                'ssn' => $data->ssn,
            ], fn (mixed $value): bool => $value !== null));

            $physician->save();
            $physician->departments()->sync($data->departmentIds);

            return $physician->load('departments:id,name');
        });
    }
}
