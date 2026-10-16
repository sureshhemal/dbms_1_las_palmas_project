<?php

namespace App\Staff\UseCases;

use App\Staff\Entities\Models\Physician;

final class ShowPhysician
{
    public function execute(Physician $physician): Physician
    {
        return $physician
            ->load([
                'departments:id,name',
                'headedDepartments:id,deptID,name,head_id',
            ])
            ->loadCount('primaryPatients');
    }
}
