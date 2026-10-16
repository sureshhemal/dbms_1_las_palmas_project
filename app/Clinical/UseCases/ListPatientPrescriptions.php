<?php

namespace App\Clinical\UseCases;

use App\Clinical\Entities\Models\Prescription;
use App\Patient\Entities\Models\Patient;
use Illuminate\Pagination\LengthAwarePaginator;

final class ListPatientPrescriptions
{
    public function execute(Patient $patient, int $perPage, int $page): LengthAwarePaginator
    {
        return Prescription::query()
            ->with([
                'medication:id,medID,name,deleted_at',
                'physician:id,physicianID,name,deleted_at',
            ])
            ->whereBelongsTo($patient)
            ->orderByDesc('prescribedDate')
            ->latest()
            ->latest('id')
            ->paginate($perPage, ['*'], 'page', $page);
    }
}
