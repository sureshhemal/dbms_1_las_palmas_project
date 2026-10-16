<?php

namespace App\Clinical\IO\Http\Requests;

use App\Clinical\Entities\PerformedProcedureStatus;
use App\Clinical\IO\Http\DTOs\PerformedProcedureFilters;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;

class ListPerformedProceduresRequest extends FormRequest
{
    public function getFilters(): PerformedProcedureFilters
    {
        return new PerformedProcedureFilters(
            search: $this->string('search')->trim()->toString() ?: null,
            years: collect($this->array('years'))
                ->filter(fn (mixed $year): bool => is_numeric($year))
                ->map(fn (mixed $year): int => (int) $year)
                ->values()
                ->all(),
            statuses: collect($this->array('statuses'))
                ->map(fn (mixed $status): ?PerformedProcedureStatus => PerformedProcedureStatus::tryFrom((string) $status))
                ->filter()
                ->values()
                ->all(),
            physicianIds: $this->ids('physician_ids'),
            nurseIds: $this->ids('nurse_ids'),
            procedureIds: $this->ids('procedure_ids'),
        );
    }

    private function ids(string $key): array
    {
        return collect($this->array($key))
            ->filter(fn (mixed $id): bool => Str::isUlid($id))
            ->values()
            ->all();
    }
}
