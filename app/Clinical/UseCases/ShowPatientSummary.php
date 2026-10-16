<?php

namespace App\Clinical\UseCases;

use App\Clinical\Entities\Models\PerformedProcedure;
use App\Clinical\Entities\Models\Prescription;
use App\Clinical\Entities\PatientSummary;
use App\Patient\Entities\Models\Patient;

final class ShowPatientSummary
{
    public function execute(Patient $patient): PatientSummary
    {
        $nextProcedure = PerformedProcedure::query()
            ->with('procedure:id,name')
            ->whereBelongsTo($patient)
            ->whereScheduled()
            ->orderBy('procDate')
            ->first(['id', 'patient_id', 'procedure_id', 'procDate']);

        return new PatientSummary(
            nextProcedure: $nextProcedure instanceof PerformedProcedure ? $nextProcedure : null,
            performedProceduresCount: PerformedProcedure::query()
                ->whereBelongsTo($patient)
                ->wherePerformed()
                ->count(),
            prescriptionsCount: Prescription::query()
                ->whereBelongsTo($patient)
                ->count(),
            totalRevenue: (float) PerformedProcedure::query()
                ->join('Procedure', 'Procedure.id', '=', 'Undergoes.procedure_id')
                ->whereBelongsTo($patient)
                ->wherePerformed()
                ->sum('Procedure.cost'),
        );
    }
}
