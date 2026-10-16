<?php

namespace App\Clinical\IO\Http\DTOs;

final readonly class PerformedProcedureFilters
{
    public function __construct(
        public ?string $search = null,
        public array $years = [],
        public array $statuses = [],
        public array $physicianIds = [],
        public array $nurseIds = [],
        public array $procedureIds = [],
    ) {}
}
