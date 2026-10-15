<?php

namespace App\Clinical\IO\Http\DTOs;

final readonly class MedicationData
{
    public function __construct(
        public ?int $medID,
        public ?string $name,
    ) {}
}
