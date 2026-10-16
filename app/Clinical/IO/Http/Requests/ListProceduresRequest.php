<?php

namespace App\Clinical\IO\Http\Requests;

use App\Clinical\Entities\ProcedureSortColumn;
use App\Clinical\IO\Http\DTOs\ProcedureSorting;
use App\Foundation\Entities\SortDirection;
use Illuminate\Foundation\Http\FormRequest;

class ListProceduresRequest extends FormRequest
{
    public function getSorting(): ProcedureSorting
    {
        return new ProcedureSorting(
            column: ProcedureSortColumn::tryFrom($this->string('sort')->toString()) ?? ProcedureSortColumn::Name,
            direction: SortDirection::tryFrom($this->string('direction')->toString()) ?? SortDirection::Ascending,
        );
    }
}
