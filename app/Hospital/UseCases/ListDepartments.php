<?php

namespace App\Hospital\UseCases;

use App\Hospital\Entities\Models\Department;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

final class ListDepartments
{
    public function execute(bool $withHead = false): Collection
    {
        return Department::query()
            ->when($withHead, fn (Builder $query) => $query->with('head:id,name'))
            ->orderBy('deptID')
            ->get();
    }
}
