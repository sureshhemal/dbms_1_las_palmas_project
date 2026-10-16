<?php

namespace App\Clinical\UseCases;

use App\Clinical\Entities\Models\PerformedProcedure;
use App\Staff\Entities\Models\Physician;
use Illuminate\Pagination\LengthAwarePaginator;

final class ListPhysicianPerformedProcedures
{
    public const array RELATIONS = [
        'patient:id,name',
        'procedure:id,name',
        'stay:id,stayID,room_id',
        'stay.room:id,roomID',
        'nurse:id,nurseID,name',
    ];

    public function execute(Physician $physician, int $perPage, int $page): LengthAwarePaginator
    {
        return PerformedProcedure::query()
            ->with(self::RELATIONS)
            ->whereBelongsTo($physician)
            ->wherePerformed()
            ->orderByDesc('procDate')
            ->latest()
            ->latest('id')
            ->paginate($perPage, ['*'], 'page', $page);
    }
}
