<?php

namespace App\Clinical\UseCases;

use App\Clinical\Entities\ClinicalSummary;
use App\Clinical\Entities\Models\Appointment;
use App\Clinical\Entities\Models\PerformedProcedure;
use App\Clinical\Entities\Models\Prescription;
use App\Patient\Entities\Models\Patient;
use App\Staff\Entities\Models\Physician;

final class ShowClinicalSummary
{
    public function execute(Patient|Physician $subject): ClinicalSummary
    {
        $nextProcedure = PerformedProcedure::query()
            ->with('procedure:id,name')
            ->whereBelongsTo($subject)
            ->whereScheduled()
            ->orderBy('procDate')
            ->first(['id', 'patient_id', 'procedure_id', 'procDate']);

        $nextAppointment = Appointment::query()
            ->whereBelongsTo($subject)
            ->whereUpcoming()
            ->orderBy('startDateTime')
            ->first(['id', 'startDateTime', 'endDateTime']);

        return new ClinicalSummary(
            nextProcedure: $nextProcedure instanceof PerformedProcedure ? $nextProcedure : null,
            performedProceduresCount: PerformedProcedure::query()
                ->whereBelongsTo($subject)
                ->wherePerformed()
                ->count(),
            prescriptionsCount: Prescription::query()
                ->whereBelongsTo($subject)
                ->count(),
            totalRevenue: (float) PerformedProcedure::query()
                ->join('Procedure', 'Procedure.id', '=', 'Undergoes.procedure_id')
                ->whereBelongsTo($subject)
                ->wherePerformed()
                ->sum('Procedure.cost'),
            nextAppointment: $nextAppointment instanceof Appointment ? $nextAppointment : null,
            completedAppointmentsCount: Appointment::query()
                ->whereBelongsTo($subject)
                ->whereCompleted()
                ->count(),
        );
    }
}
