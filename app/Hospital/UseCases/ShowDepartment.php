<?php

namespace App\Hospital\UseCases;

use App\Hospital\Entities\Models\Department;

final class ShowDepartment
{
    public function execute(Department $department): Department
    {
        return $department->load('head:id,physicianID,name,position');
    }
}
