<?php

namespace App\Clinical\IO\Http\DTOs;

final readonly class StayFilters
{
    public function __construct(
        public ?string $search = null,
        public array $statuses = [],
    ) {}
}
