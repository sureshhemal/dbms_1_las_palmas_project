<?php

namespace App\Hospital\IO\Http\Controllers;

use App\Hospital\UseCases\ListDepartmentHeadCandidates;
use Illuminate\Http\JsonResponse;

class DepartmentHeadCandidatesController
{
    public function index(ListDepartmentHeadCandidates $listDepartmentHeadCandidates): JsonResponse
    {
        return response()->json($listDepartmentHeadCandidates->execute());
    }
}
