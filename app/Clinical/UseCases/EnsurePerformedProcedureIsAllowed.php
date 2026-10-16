<?php

namespace App\Clinical\UseCases;

use App\Clinical\Entities\Models\Stay;
use App\Clinical\IO\Http\DTOs\ProcedureDetails;
use Illuminate\Validation\ValidationException;

final class EnsurePerformedProcedureIsAllowed
{
    public function execute(Stay $stay, ProcedureDetails $procedure): void
    {
        $problems = array_filter([
            'procDate' => $this->problemWithDate($stay, $procedure),
            'physician_id' => $this->problemWithPhysician($procedure),
        ]);

        if (! blank($problems)) {
            throw ValidationException::withMessages($problems);
        }
    }

    private function problemWithDate(Stay $stay, ProcedureDetails $procedure): ?string
    {
        $admittedOn = $stay->startDate->toDateString();
        $leavingOn = $stay->endDate?->toDateString();
        $procDate = $procedure->procDate->toDateString();
        $isDuringTheStay = $procDate >= $admittedOn && ($leavingOn === null || $procDate <= $leavingOn);

        return $isDuringTheStay
            ? null
            : "The procedure must be during the stay ({$admittedOn} to ".($leavingOn ?? 'now').').';
    }

    private function problemWithPhysician(ProcedureDetails $procedure): ?string
    {
        return $procedure->physician->position->isCapableOfPrescribing()
            ? null
            : "{$procedure->physician->name} is an intern and cannot perform procedures.";
    }
}
