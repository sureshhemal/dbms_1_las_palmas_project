<?php

namespace App\Hospital\UseCases;

use App\Hospital\Entities\Models\Department;
use App\Hospital\IO\Http\DTOs\DepartmentData;

final class UpdateDepartment
{
    public function execute(Department $department, DepartmentData $data): Department
    {
        $department->update([
            'deptID' => $data->deptID,
            'head_id' => $data->headId,
        ]);

        return $department->load('head');
    }
}
