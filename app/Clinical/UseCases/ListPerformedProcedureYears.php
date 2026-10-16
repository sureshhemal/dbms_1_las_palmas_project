<?php

namespace App\Clinical\UseCases;

use App\Clinical\Entities\Models\PerformedProcedure;

final class ListPerformedProcedureYears
{
    public function execute(): array
    {
        return PerformedProcedure::query()
            ->selectRaw('distinct year(procDate) as performedYear')
            ->orderByDesc('performedYear')
            ->pluck('performedYear')
            ->map(fn (mixed $performedYear): int => (int) $performedYear)
            ->all();
    }
}
