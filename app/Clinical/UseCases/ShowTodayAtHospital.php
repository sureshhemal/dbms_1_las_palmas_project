<?php

namespace App\Clinical\UseCases;

use App\Clinical\Entities\Models\Appointment;
use App\Clinical\Entities\Models\PerformedProcedure;
use App\Clinical\Entities\Models\Stay;
use App\Clinical\Entities\TodayAtHospital;
use App\Hospital\Entities\HospitalCalendar;
use App\Staff\Entities\Models\OnCall;
use Carbon\CarbonImmutable;

final class ShowTodayAtHospital
{
    private const string PATIENT_WITH_PHONE = 'patient:id,patientID,name,phone,deleted_at';

    public function execute(): TodayAtHospital
    {
        $today = HospitalCalendar::today();
        $startOfDay = CarbonImmutable::parse($today->toDateString(), HospitalCalendar::timezone())->utc();

        return new TodayAtHospital(
            date: $today->toDateString(),
            procedures: PerformedProcedure::query()
                ->with([
                    self::PATIENT_WITH_PHONE,
                    'procedure:id,procID,name',
                    'stay:id,stayID,room_id',
                    'stay.room:id,roomID',
                    'physician:id,physicianID,name,deleted_at',
                    'nurse:id,nurseID,name,deleted_at',
                ])
                ->where('procDate', $today->toDateString())
                ->orderBy('created_at')
                ->orderBy('id')
                ->get(),
            appointments: Appointment::query()
                ->with([
                    self::PATIENT_WITH_PHONE,
                    'physician:id,physicianID,name,deleted_at',
                    'nurse:id,nurseID,name,deleted_at',
                ])
                ->where('startDateTime', '>=', $startOfDay->toDateTimeString())
                ->where('startDateTime', '<', $startOfDay->addDay()->toDateTimeString())
                ->orderBy('startDateTime')
                ->orderBy('id')
                ->get(),
            onCallNurses: OnCall::query()
                ->with('nurse:id,nurseID,name,position')
                ->whereOnCallOn($today)
                ->whereNurseIsOnStaff()
                ->orderBy('startDate')
                ->orderBy('id')
                ->get(),
            discharges: Stay::query()
                ->with([
                    self::PATIENT_WITH_PHONE,
                    'room:id,roomID,roomType,deleted_at',
                ])
                ->where('endDate', $today->toDateString())
                ->orderBy('startDate')
                ->orderBy('id')
                ->get(),
        );
    }
}
