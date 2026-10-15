<?php

namespace App\Staff\UseCases;

use App\Staff\Entities\Models\Nurse;
use Illuminate\Database\Eloquent\Collection;

final class ListOnCallNurseCandidates
{
    public function execute(): Collection
    {
        return Nurse::query()
            ->select(['id', 'nurseID', 'name', 'position'])
            ->orderBy('name')
            ->get();
    }
}
