<?php

namespace App\Clinical\IO\Http\Requests;

use App\Clinical\Entities\MedicationSortColumn;
use App\Clinical\IO\Http\DTOs\MedicationSorting;
use App\Foundation\Entities\SortDirection;
use Illuminate\Foundation\Http\FormRequest;

class ListMedicationsRequest extends FormRequest
{
    public function getSorting(): MedicationSorting
    {
        return new MedicationSorting(
            column: MedicationSortColumn::tryFrom($this->string('sort')->toString()) ?? MedicationSortColumn::Name,
            direction: SortDirection::tryFrom($this->string('direction')->toString()) ?? SortDirection::Ascending,
        );
    }
}
