<?php

namespace App\Staff\UseCases;

use App\Staff\Entities\Models\Physician;
use App\Staff\IO\Http\DTOs\PhysicianData;
use Illuminate\Support\Facades\DB;

final class UpdatePhysician
{
    public function execute(Physician $physician, PhysicianData $data): Physician
    {
        return DB::transaction(function () use ($physician, $data): Physician {
            $physician->update([
                'physicianID' => $data->physicianID ?? $physician->physicianID,
                'name' => $data->name,
                'position' => $data->position,
                'ssn' => $data->ssn,
            ]);

            $physician->departments()->sync($data->departmentIds);

            return $physician->load('departments:id,name');
        });
    }
}
