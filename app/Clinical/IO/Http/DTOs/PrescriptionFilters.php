<?php

namespace App\Clinical\IO\Http\DTOs;

final readonly class PrescriptionFilters
{
    public function __construct(
        public ?string $search = null,
        public array $years = [],
    ) {}
}
