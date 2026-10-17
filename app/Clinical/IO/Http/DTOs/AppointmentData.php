<?php

namespace App\Clinical\IO\Http\DTOs;

use App\Patient\Entities\Models\Patient;
use App\Staff\Entities\Models\Nurse;
use App\Staff\Entities\Models\Physician;
use Carbon\CarbonImmutable;

final readonly class AppointmentData
{
    public function __construct(
        public Patient $patient,
        public Physician $physician,
        public Nurse $nurse,
        public CarbonImmutable $startDateTime,
        public ?CarbonImmutable $endDateTime,
    ) {}
}
