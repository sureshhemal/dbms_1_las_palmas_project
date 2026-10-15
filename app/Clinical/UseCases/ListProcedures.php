<?php

namespace App\Clinical\UseCases;

use App\Clinical\Entities\Models\Procedure;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\LengthAwarePaginator;

final class ListProcedures
{
    public function execute(int $perPage, int $page, ?string $search = null): LengthAwarePaginator
    {
        return Procedure::query()
            ->when($search, fn (Builder $query) => $query->whereAny(['name', 'procID'], 'like', "%{$search}%"))
            ->orderBy('name')
            ->orderBy('procID')
            ->paginate($perPage, ['*'], 'page', $page);
    }
}
