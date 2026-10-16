<?php

namespace App\Clinical\IO\Http\Controllers;

use App\Clinical\UseCases\ListPrescriberCandidates;
use Illuminate\Http\JsonResponse;

class PrescriberCandidatesController
{
    public function index(ListPrescriberCandidates $listPrescriberCandidates): JsonResponse
    {
        return response()->json($listPrescriberCandidates->execute());
    }
}
