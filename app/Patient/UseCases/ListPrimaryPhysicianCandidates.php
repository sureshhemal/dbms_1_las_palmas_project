<?php

namespace App\Patient\UseCases;

use App\Staff\Entities\Models\Physician;
use Illuminate\Database\Eloquent\Collection;

final class ListPrimaryPhysicianCandidates
{
    public function execute(): Collection
    {
        return Physician::query()
            ->select(['id', 'physicianID', 'name'])
            ->whereCapableOfBeingPrimaryPhysicianBasedOnPosition()
            ->orderBy('name')
            ->get();
    }
}
