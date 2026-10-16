<?php

namespace App\Clinical\UseCases;

use App\Clinical\Entities\Models\PerformedProcedure;
use App\Patient\Entities\Models\Patient;
use Illuminate\Pagination\LengthAwarePaginator;

final class ListPatientPerformedProcedures
{
    public const array RELATIONS = [
        'procedure:id,procID,name',
        'stay:id,stayID,room_id',
        'stay.room:id,roomID',
        'physician:id,physicianID,name',
        'nurse:id,nurseID,name',
    ];

    public function execute(Patient $patient, int $perPage, int $page): LengthAwarePaginator
    {
        return PerformedProcedure::query()
            ->with(self::RELATIONS)
            ->whereBelongsTo($patient)
            ->wherePerformed()
            ->orderByDesc('procDate')
            ->latest()
            ->latest('id')
            ->paginate($perPage, ['*'], 'page', $page);
    }
}
