<?php

namespace App\Patient\UseCases;

use App\Patient\Entities\Models\Patient;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\LengthAwarePaginator;

final class ListPatients
{
    public function execute(int $perPage, int $page, ?string $search = null, array $primaryPhysicianIds = []): LengthAwarePaginator
    {
        return Patient::query()
            ->with('primaryPhysician:id,physicianID,name,deleted_at')
            ->when($search, fn (Builder $query) => $query->whereAny(['name', 'patientID', 'phone', 'insuranceNumber'], 'like', "%{$search}%"))
            ->when($primaryPhysicianIds, fn (Builder $query) => $query->whereIn('primary_physician_id', $primaryPhysicianIds))
            ->latest()
            ->latest('id')
            ->paginate($perPage, ['*'], 'page', $page);
    }
}
