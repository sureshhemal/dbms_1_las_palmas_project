<?php

namespace App\Clinical\UseCases;

use App\Staff\Entities\Models\Physician;
use Illuminate\Database\Eloquent\Collection;

final class ListPrescriberCandidates
{
    public function execute(): Collection
    {
        return Physician::query()
            ->select(['id', 'physicianID', 'name'])
            ->whereCapableOfPrescribingBasedOnPosition()
            ->orderBy('name')
            ->get();
    }
}
