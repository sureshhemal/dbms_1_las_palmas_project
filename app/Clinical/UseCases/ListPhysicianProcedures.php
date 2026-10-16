<?php

namespace App\Clinical\UseCases;

use App\Clinical\Entities\Models\PerformedProcedure;
use App\Clinical\Entities\ProcedureShare;
use App\Hospital\Entities\HospitalCalendar;
use App\Staff\Entities\Models\Physician;
use stdClass;

final class ListPhysicianProcedures
{
    public function execute(Physician $physician): array
    {
        return PerformedProcedure::query()
            ->join('Procedure', 'Procedure.id', '=', 'Undergoes.procedure_id')
            ->whereBelongsTo($physician)
            ->wherePerformed()
            ->groupBy('Procedure.id')
            ->select(['Procedure.id', 'Procedure.name', 'Procedure.deleted_at'])
            ->selectRaw('count(*) as performedCount')
            ->selectRaw('round(100 * count(*) / (select count(*) from Undergoes where physician_id = ? and procDate <= ? and deleted_at is null), 1) as percentage', [
                $physician->id,
                HospitalCalendar::today()->toDateString(),
            ])
            ->orderByDesc('performedCount')
            ->orderBy('Procedure.name')
            ->toBase()
            ->get()
            ->map(fn (stdClass $procedure): ProcedureShare => new ProcedureShare(
                procedureId: $procedure->id,
                name: $procedure->name,
                isRemoved: $procedure->deleted_at !== null,
                performedCount: (int) $procedure->performedCount,
                percentage: (float) $procedure->percentage,
            ))
            ->all();
    }
}
