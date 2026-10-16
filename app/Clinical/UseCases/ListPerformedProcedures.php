<?php

namespace App\Clinical\UseCases;

use App\Clinical\Entities\Models\PerformedProcedure;
use App\Clinical\IO\Database\QueryBuilders\PerformedProceduresQueryBuilder;
use App\Clinical\IO\Http\DTOs\PerformedProcedureFilters;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\LengthAwarePaginator;

final class ListPerformedProcedures
{
    public const array RELATIONS = [
        'patient:id,patientID,name',
        'procedure:id,procID,name',
        'stay:id,stayID,room_id,startDate,endDate',
        'stay.room:id,roomID',
        'physician:id,physicianID,name',
        'nurse:id,nurseID,name',
    ];

    public function execute(int $perPage, int $page, PerformedProcedureFilters $filters): LengthAwarePaginator
    {
        $search = $filters->search;

        return PerformedProcedure::query()
            ->with(self::RELATIONS)
            ->when($search, fn (Builder $query) => $query->whereHas('patient', fn (Builder $patient) => $patient->whereAny(['name', 'patientID'], 'like', "%{$search}%")))
            ->when($filters->physicianIds, fn (Builder $query) => $query->whereIn('physician_id', $filters->physicianIds))
            ->when($filters->nurseIds, fn (Builder $query) => $query->whereIn('nurse_id', $filters->nurseIds))
            ->when($filters->procedureIds, fn (Builder $query) => $query->whereIn('procedure_id', $filters->procedureIds))
            ->when($filters->years, fn (Builder $query) => $query->where(function (Builder $inYears) use ($filters): void {
                foreach ($filters->years as $year) {
                    $inYears->orWhereBetween('procDate', ["{$year}-01-01", "{$year}-12-31"]);
                }
            }))
            ->when($filters->statuses, fn (PerformedProceduresQueryBuilder $query) => $query->whereStatusIn($filters->statuses))
            ->orderByDesc('procDate')
            ->latest()
            ->latest('id')
            ->paginate($perPage, ['*'], 'page', $page);
    }
}
