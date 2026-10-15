<?php

namespace App\Staff\UseCases;

use App\Staff\Entities\Models\Nurse;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\LengthAwarePaginator;

final class ListNurses
{
    public function execute(int $perPage, int $page, ?string $search = null, array $positions = []): LengthAwarePaginator
    {
        return Nurse::query()
            ->when($search, fn (Builder $query) => $query->whereAny(['name', 'nurseID'], 'like', "%{$search}%"))
            ->when($positions, fn (Builder $query) => $query->whereIn('position', $positions))
            ->latest()
            ->latest('id')
            ->paginate($perPage, ['*'], 'page', $page);
    }
}
