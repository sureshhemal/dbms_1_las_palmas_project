<?php

namespace App\Staff\UseCases;

use App\Staff\Entities\Models\Physician;
use App\Staff\IO\Database\QueryBuilders\PhysiciansQueryBuilder;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

final class ListPhysicians
{
    public function execute(?string $search = null, array $positions = [], array $departmentIds = []): Collection
    {
        return Physician::query()
            ->with([
                'headedDepartments:id,deptID,name,head_id',
                'departments:id,name',
            ])
            ->when($search, fn (Builder $query) => $query->whereAny(['name', 'physicianID'], 'like', "%{$search}%"))
            ->when($positions, fn (Builder $query) => $query->whereIn('position', $positions))
            ->when($departmentIds, fn (PhysiciansQueryBuilder $query) => $query->whereAffiliatedWithAnyDepartmentOf($departmentIds))
            ->latest()
            ->latest('id')
            ->get();
    }
}
