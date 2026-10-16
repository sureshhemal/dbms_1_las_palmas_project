<?php

namespace App\Clinical\UseCases;

use App\Clinical\Entities\Models\PerformedProcedure;
use App\Clinical\Entities\Models\Stay;
use App\Clinical\IO\Http\DTOs\DischargeData;
use App\Hospital\Entities\HospitalCalendar;
use Illuminate\Validation\ValidationException;

final class DischargePatient
{
    public function execute(DischargeData $data): Stay
    {
        $stay = $data->stay;
        $lastProcedureOn = PerformedProcedure::query()->where('stay_id', $stay->id)->max('procDate');

        $problem = match (true) {
            $stay->endDate !== null && $stay->endDate->lessThan(HospitalCalendar::today()) => "This stay already ended on {$stay->endDate->toDateString()}.",
            is_string($lastProcedureOn) && $data->endDate->toDateString() < $lastProcedureOn => "The stay has a procedure on {$lastProcedureOn}, so it cannot end earlier.",
            default => null,
        };

        if ($problem !== null) {
            throw ValidationException::withMessages(['endDate' => $problem]);
        }

        $stay->update(['endDate' => $data->endDate->toDateString()]);

        return $stay->load(ListStays::RELATIONS);
    }
}
