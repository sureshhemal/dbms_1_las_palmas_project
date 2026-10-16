<?php

namespace App\Clinical\UseCases;

use App\Clinical\Entities\Models\Prescription;

final class ListPrescriptionYears
{
    public function execute(): array
    {
        return Prescription::query()
            ->selectRaw('distinct year(prescribedDate) as prescribedYear')
            ->orderByDesc('prescribedYear')
            ->pluck('prescribedYear')
            ->map(fn (mixed $prescribedYear): int => (int) $prescribedYear)
            ->all();
    }
}
