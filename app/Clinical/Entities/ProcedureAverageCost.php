<?php

namespace App\Clinical\Entities;

final readonly class ProcedureAverageCost
{
    public function __construct(
        public ?float $averageCost,
        public int $pricedProcedureCount,
        public int $unpricedProcedureCount,
    ) {}
}
