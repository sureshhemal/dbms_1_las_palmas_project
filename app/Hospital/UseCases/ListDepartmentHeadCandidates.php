<?php

namespace App\Hospital\UseCases;

use App\Staff\Entities\Models\Physician;
use Illuminate\Database\Eloquent\Collection;

final class ListDepartmentHeadCandidates
{
    public function execute(): Collection
    {
        return Physician::query()
            ->select(['id', 'name', 'physicianID'])
            ->whereCapableOfBeingDepartmentHeadBasedOnPosition()
            ->orderBy('name')
            ->get();
    }
}
