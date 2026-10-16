<?php

namespace App\Clinical\UseCases;

use App\Clinical\Entities\Models\PerformedProcedure;
use App\Patient\Entities\Models\Patient;
use Illuminate\Pagination\LengthAwarePaginator;

final class ListPatientUpcomingProcedures
{
    public function execute(Patient $patient, int $perPage, int $page): LengthAwarePaginator
    {
        return PerformedProcedure::query()
            ->with(ListPatientPerformedProcedures::RELATIONS)
            ->whereBelongsTo($patient)
            ->whereScheduled()
            ->orderBy('procDate')
            ->oldest()
            ->oldest('id')
            ->paginate($perPage, ['*'], 'page', $page);
    }
}
