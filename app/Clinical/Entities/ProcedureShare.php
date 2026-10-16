<?php

namespace App\Clinical\Entities;

final readonly class ProcedureShare
{
    public function __construct(
        public string $procedureId,
        public string $name,
        public bool $isRemoved,
        public int $performedCount,
        public float $percentage,
    ) {}
}
