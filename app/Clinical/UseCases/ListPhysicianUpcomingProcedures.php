<?php

namespace App\Clinical\UseCases;

use App\Clinical\Entities\Models\PerformedProcedure;
use App\Staff\Entities\Models\Physician;
use Illuminate\Pagination\LengthAwarePaginator;

final class ListPhysicianUpcomingProcedures
{
    public function execute(Physician $physician, int $perPage, int $page): LengthAwarePaginator
    {
        return PerformedProcedure::query()
            ->with(ListPhysicianPerformedProcedures::RELATIONS)
            ->whereBelongsTo($physician)
            ->whereScheduled()
            ->orderBy('procDate')
            ->oldest()
            ->oldest('id')
            ->paginate($perPage, ['*'], 'page', $page);
    }
}
