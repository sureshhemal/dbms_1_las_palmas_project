<?php

namespace App\Hospital\IO\Http\Controllers;

use App\Hospital\Entities\Models\Department;
use App\Hospital\IO\Http\Requests\DepartmentRequest;
use App\Hospital\UseCases\ListDepartments;
use App\Hospital\UseCases\UpdateDepartment;
use Illuminate\Http\JsonResponse;

class DepartmentsController
{
    public function index(ListDepartments $listDepartments): JsonResponse
    {
        return response()->json($listDepartments->execute());
    }

    public function update(Department $department, DepartmentRequest $request, UpdateDepartment $updateDepartment): JsonResponse
    {
        return response()->json($updateDepartment->execute($department, $request->getData()));
    }
}
