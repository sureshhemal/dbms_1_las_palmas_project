<?php

namespace App\Staff\IO\Http\Controllers;

use App\Staff\UseCases\ListOnCallNurseCandidates;
use Illuminate\Http\JsonResponse;

class OnCallNurseCandidatesController
{
    public function index(ListOnCallNurseCandidates $listOnCallNurseCandidates): JsonResponse
    {
        return response()->json($listOnCallNurseCandidates->execute());
    }
}
