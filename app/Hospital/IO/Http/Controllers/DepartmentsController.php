<?php

namespace App\Hospital\IO\Http\Controllers;

use App\Hospital\Entities\Models\Department;
use App\Hospital\IO\Http\Requests\DepartmentRequest;
use App\Hospital\UseCases\ListDepartments;
use App\Hospital\UseCases\ShowDepartment;
use App\Hospital\UseCases\UpdateDepartment;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DepartmentsController
{
    public function index(Request $request, ListDepartments $listDepartments): JsonResponse
    {
        return response()->json($listDepartments->execute(
            withHead: $request->boolean('with_head'),
        ));
    }

    public function show(Department $department, ShowDepartment $showDepartment): JsonResponse
    {
        return response()->json($showDepartment->execute($department));
    }

    public function update(Department $department, DepartmentRequest $request, UpdateDepartment $updateDepartment): JsonResponse
    {
        return response()->json($updateDepartment->execute($department, $request->getData()));
    }
}
