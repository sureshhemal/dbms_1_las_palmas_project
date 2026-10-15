<?php

namespace App\Staff\UseCases;

use App\Staff\Entities\Models\Physician;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

final class ListPhysicians
{
    public function execute(?string $search = null, array $positions = []): Collection
    {
        return Physician::query()
            ->when($search, fn (Builder $query) => $query->whereAny(['name', 'physicianID'], 'like', "%{$search}%"))
            ->when($positions, fn (Builder $query) => $query->whereIn('position', $positions))
            ->latest()
            ->latest('id')
            ->get();
    }
}
