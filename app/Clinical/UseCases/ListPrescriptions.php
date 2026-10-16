<?php

namespace App\Clinical\UseCases;

use App\Clinical\Entities\Models\Prescription;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\LengthAwarePaginator;

final class ListPrescriptions
{
    public const array RELATIONS = [
        'patient:id,patientID,name,deleted_at',
        'medication:id,medID,name,deleted_at',
        'physician:id,physicianID,name,deleted_at',
    ];

    public function execute(int $perPage, int $page, ?string $search = null, array $years = []): LengthAwarePaginator
    {
        return Prescription::query()
            ->with(self::RELATIONS)
            ->when($search, fn (Builder $query) => $query->where(fn (Builder $matches) => $matches
                ->whereHas('patient', fn (Builder $patient) => $patient->whereAny(['name', 'patientID'], 'like', "%{$search}%"))
                ->orWhereHas('medication', fn (Builder $medication) => $medication->where('name', 'like', "%{$search}%"))
                ->orWhereHas('physician', fn (Builder $physician) => $physician->where('name', 'like', "%{$search}%"))))
            ->when($years, fn (Builder $query) => $query->where(function (Builder $inYears) use ($years): void {
                foreach ($years as $year) {
                    $inYears->orWhereBetween('prescribedDate', ["{$year}-01-01", "{$year}-12-31"]);
                }
            }))
            ->orderByDesc('prescribedDate')
            ->latest()
            ->latest('id')
            ->paginate($perPage, ['*'], 'page', $page);
    }
}
