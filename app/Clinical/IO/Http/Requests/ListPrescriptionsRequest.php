<?php

namespace App\Clinical\IO\Http\Requests;

use App\Clinical\IO\Http\DTOs\PrescriptionFilters;
use Illuminate\Foundation\Http\FormRequest;

class ListPrescriptionsRequest extends FormRequest
{
    public function getFilters(): PrescriptionFilters
    {
        return new PrescriptionFilters(
            search: $this->string('search')->trim()->toString() ?: null,
            years: collect($this->array('years'))
                ->filter(fn (mixed $year): bool => is_numeric($year))
                ->map(fn (mixed $year): int => (int) $year)
                ->values()
                ->all(),
        );
    }
}
