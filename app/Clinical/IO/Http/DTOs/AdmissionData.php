<?php

namespace App\Clinical\IO\Http\DTOs;

use App\Hospital\Entities\Models\Room;
use App\Patient\Entities\Models\Patient;
use Carbon\CarbonImmutable;

final readonly class AdmissionData
{
    public function __construct(
        public Patient $patient,
        public Room $room,
        public CarbonImmutable $startDate,
        public ?CarbonImmutable $endDate = null,
    ) {}
}
