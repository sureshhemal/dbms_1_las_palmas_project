<?php

namespace App\Clinical\IO\Http\DTOs;

use App\Clinical\Entities\MedicationSortColumn;
use App\Foundation\Entities\SortDirection;

final readonly class MedicationSorting
{
    public function __construct(
        public MedicationSortColumn $column = MedicationSortColumn::Name,
        public SortDirection $direction = SortDirection::Ascending,
    ) {}
}
