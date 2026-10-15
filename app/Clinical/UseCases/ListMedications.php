<?php

namespace App\Clinical\UseCases;

use App\Clinical\Entities\Models\Medication;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\LengthAwarePaginator;

final class ListMedications
{
    public function execute(int $perPage, int $page, ?string $search = null): LengthAwarePaginator
    {
        return Medication::query()
            ->when($search, fn (Builder $query) => $query->whereAny(['name', 'medID'], 'like', "%{$search}%"))
            ->orderBy('name')
            ->orderBy('medID')
            ->paginate($perPage, ['*'], 'page', $page);
    }
}
