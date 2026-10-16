<?php

namespace App\Clinical\IO\Http\DTOs;

use App\Clinical\Entities\ProcedureSortColumn;
use App\Foundation\Entities\SortDirection;

final readonly class ProcedureSorting
{
    public function __construct(
        public ProcedureSortColumn $column = ProcedureSortColumn::Name,
        public SortDirection $direction = SortDirection::Ascending,
    ) {}
}
