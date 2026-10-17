<?php

namespace App\Clinical\Entities;

use Illuminate\Support\Collection;

final readonly class TodayAtHospital
{
    public function __construct(
        public string $date,
        public Collection $procedures,
        public Collection $appointments,
        public Collection $onCallNurses,
        public Collection $discharges,
    ) {}
}
