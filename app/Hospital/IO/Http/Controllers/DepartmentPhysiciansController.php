<?php

namespace App\Hospital\IO\Http\Controllers;

use App\Hospital\Entities\Models\Department;
use App\Hospital\UseCases\ListDepartmentPhysicians;
use Illuminate\Http\JsonResponse;

class DepartmentPhysiciansController
{
    public function index(Department $department, ListDepartmentPhysicians $listDepartmentPhysicians): JsonResponse
    {
        return response()->json($listDepartmentPhysicians->execute($department));
    }
}
