<?php

namespace App\Clinical\Entities;

final readonly class MedicationShare
{
    public function __construct(
        public string $medicationId,
        public string $name,
        public bool $isRemoved,
        public int $prescriptionsCount,
        public float $percentage,
    ) {}
}
