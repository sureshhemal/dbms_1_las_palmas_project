<?php

namespace App\Clinical\UseCases;

use App\Clinical\Entities\Models\Appointment;
use App\Hospital\Entities\HospitalCalendar;
use Carbon\CarbonImmutable;

final class ListAppointmentYears
{
    public function execute(): array
    {
        $earliest = Appointment::query()->min('startDateTime');
        $latest = Appointment::query()->max('startDateTime');

        if ($earliest === null || $latest === null) {
            return [];
        }

        return range($this->hospitalYearOf($latest), $this->hospitalYearOf($earliest));
    }

    private function hospitalYearOf(mixed $dateTime): int
    {
        return CarbonImmutable::parse((string) $dateTime, 'UTC')->setTimezone(HospitalCalendar::timezone())->year;
    }
}
