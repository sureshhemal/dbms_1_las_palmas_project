<?php

namespace App\Hospital\Entities;

use Carbon\CarbonImmutable;

final class HospitalCalendar
{
    public static function timezone(): string
    {
        return config()->string('app.hospital_timezone');
    }

    public static function today(): CarbonImmutable
    {
        return CarbonImmutable::parse(now(self::timezone())->toDateString());
    }
}
