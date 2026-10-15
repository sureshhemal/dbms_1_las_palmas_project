<?php

namespace App\Staff\UseCases;

use App\Hospital\Entities\HospitalCalendar;
use App\Staff\Entities\Models\Nurse;
use App\Staff\Entities\Models\OnCall;
use App\Staff\Entities\Models\Physician;

final class CountStaff
{
    public function execute(): array
    {
        return [
            'physician_count' => Physician::count(),
            'nurse_count' => Nurse::count(),
            'on_call_nurse_count' => OnCall::query()
                ->whereNurseIsOnStaff()
                ->whereOnCallOn(HospitalCalendar::today())
                ->distinct()
                ->count('nurse_id'),
        ];
    }
}
