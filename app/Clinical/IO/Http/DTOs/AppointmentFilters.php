<?php

namespace App\Clinical\IO\Http\DTOs;

final readonly class AppointmentFilters
{
    public function __construct(
        public ?string $search = null,
        public array $years = [],
        public array $statuses = [],
        public array $physicianIds = [],
    ) {}
}
