<?php

namespace App\Clinical\UseCases;

use App\Clinical\Entities\Models\Stay;
use App\Clinical\IO\Http\DTOs\DischargeData;
use App\Hospital\Entities\HospitalCalendar;
use Illuminate\Validation\ValidationException;

final class DischargePatient
{
    public function execute(DischargeData $data): Stay
    {
        $stay = $data->stay;

        $problem = match (true) {
            $stay->endDate !== null => "This stay was already discharged on {$stay->endDate->toDateString()}.",
            $data->endDate->greaterThan(HospitalCalendar::today()) => 'A discharge cannot be dated in the future.',
            default => null,
        };

        if ($problem !== null) {
            throw ValidationException::withMessages(['endDate' => $problem]);
        }

        $stay->update(['endDate' => $data->endDate->toDateString()]);

        return $stay->load(ListStays::RELATIONS);
    }
}
