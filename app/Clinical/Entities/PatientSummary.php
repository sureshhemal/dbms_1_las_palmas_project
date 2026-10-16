<?php

namespace App\Clinical\Entities;

use App\Clinical\Entities\Models\PerformedProcedure;

final readonly class PatientSummary
{
    public function __construct(
        public ?PerformedProcedure $nextProcedure,
        public int $performedProceduresCount,
        public int $prescriptionsCount,
        public float $totalRevenue,
    ) {}
}
