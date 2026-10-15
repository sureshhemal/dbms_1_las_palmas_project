<?php

namespace App\Patient\UseCases;

use App\Patient\Entities\Models\Patient;
use App\Patient\IO\Http\DTOs\PatientData;
use App\Staff\Entities\Models\Physician;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Validation\ValidationException;

final class EnsurePrimaryPhysicianIsEligible
{
    public function execute(PatientData $data, ?Patient $patient): void
    {
        if ($data->primaryPhysicianId === null) {
            return;
        }

        $physician = Physician::query()->withoutGlobalScope(SoftDeletingScope::class)->find($data->primaryPhysicianId);

        if (! $physician instanceof Physician) {
            return;
        }

        $isKeepingCurrentPrimaryPhysician = $patient?->primary_physician_id === $physician->id;

        if ($physician->trashed() && ! $isKeepingCurrentPrimaryPhysician) {
            throw ValidationException::withMessages([
                'primary_physician_id' => "{$physician->name} has been offboarded and cannot become a primary physician.",
            ]);
        }

        if (! $physician->position->isCapableOfBeingPrimaryPhysician() && ! $isKeepingCurrentPrimaryPhysician) {
            throw ValidationException::withMessages([
                'primary_physician_id' => "{$physician->name} is an intern and cannot be a primary physician.",
            ]);
        }
    }
}
