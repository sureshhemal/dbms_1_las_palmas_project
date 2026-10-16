<?php

namespace App\Clinical\UseCases;

use App\Clinical\Entities\Models\Prescription;
use App\Staff\Entities\Models\Physician;
use Illuminate\Pagination\LengthAwarePaginator;

final class ListPhysicianPrescriptions
{
    public function execute(Physician $physician, int $perPage, int $page): LengthAwarePaginator
    {
        return Prescription::query()
            ->with([
                'patient:id,name,deleted_at',
                'medication:id,medID,name,deleted_at',
            ])
            ->whereBelongsTo($physician)
            ->orderByDesc('prescribedDate')
            ->latest()
            ->latest('id')
            ->paginate($perPage, ['*'], 'page', $page);
    }
}
