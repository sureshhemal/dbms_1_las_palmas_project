<?php

namespace App\Patient\IO\Http\Controllers;

use App\Patient\UseCases\ListPrimaryPhysicianCandidates;
use Illuminate\Http\JsonResponse;

class PrimaryPhysicianCandidatesController
{
    public function index(ListPrimaryPhysicianCandidates $listPrimaryPhysicianCandidates): JsonResponse
    {
        return response()->json($listPrimaryPhysicianCandidates->execute());
    }
}
