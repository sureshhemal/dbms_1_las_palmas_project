<?php

namespace App\Hospital\IO\Http\DTOs;

final readonly class RoomsFilters
{
    public function __construct(
        public ?string $search = null,
        public array $roomTypes = [],
        public array $bedsFree = [],
    ) {}
}
