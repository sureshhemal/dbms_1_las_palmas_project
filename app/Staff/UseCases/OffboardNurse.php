<?php

namespace App\Staff\UseCases;

use App\Hospital\Entities\HospitalCalendar;
use App\Staff\Entities\Models\Nurse;
use App\Staff\Entities\Models\OnCall;
use Illuminate\Support\Facades\DB;

final class OffboardNurse
{
    public function execute(Nurse $nurse): void
    {
        DB::transaction(function () use ($nurse): void {
            $this->removeUpcomingOnCalls($nurse);
            $this->endCurrentOnCall($nurse);

            $nurse->delete();
        });
    }

    private function removeUpcomingOnCalls(Nurse $nurse): void
    {
        OnCall::query()
            ->where('nurse_id', $nurse->id)
            ->whereStartingOnOrAfter(HospitalCalendar::today())
            ->delete();
    }

    private function endCurrentOnCall(Nurse $nurse): void
    {
        OnCall::query()
            ->where('nurse_id', $nurse->id)
            ->whereOnCallOn(HospitalCalendar::today())
            ->update(['endDate' => HospitalCalendar::today()->toDateString()]);
    }
}
