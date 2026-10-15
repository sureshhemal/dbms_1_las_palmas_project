<?php

namespace App\Hospital\UseCases;

use App\Hospital\Entities\Models\Department;
use Illuminate\Database\Eloquent\Collection;

final class ListDepartmentPhysicians
{
    public function execute(Department $department): Collection
    {
        return $department->physicians()
            ->select([
                'Physician.id',
                'Physician.physicianID',
                'Physician.name',
                'Physician.position',
                'AffiliatedWith.created_at as joined_at',
            ])
            ->withCasts(['joined_at' => 'datetime'])
            ->orderBy('AffiliatedWith.created_at')
            ->orderBy('Physician.physicianID')
            ->get()
            ->makeHidden('pivot');
    }
}
