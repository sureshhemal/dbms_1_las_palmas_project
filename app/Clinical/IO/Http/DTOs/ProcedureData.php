<?php

namespace App\Clinical\IO\Http\DTOs;

final readonly class ProcedureData
{
    public function __construct(
        public ?int $procID,
        public ?string $name,
        public ?float $cost,
    ) {}
}
