<?php

namespace App\Hospital\UseCases;

use App\Hospital\Entities\Models\Department;
use Illuminate\Database\Eloquent\Collection;

final class ListDepartments
{
    public function execute(): Collection
    {
        return Department::query()
            ->with('head:id,name')
            ->orderBy('deptID')
            ->get();
    }
}
