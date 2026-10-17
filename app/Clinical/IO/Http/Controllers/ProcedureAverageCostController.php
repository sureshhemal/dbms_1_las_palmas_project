<?php

namespace App\Clinical\IO\Http\Controllers;

use App\Clinical\UseCases\ShowProcedureAverageCost;
use Illuminate\Http\JsonResponse;

class ProcedureAverageCostController
{
    public function show(ShowProcedureAverageCost $showProcedureAverageCost): JsonResponse
    {
        return response()->json($showProcedureAverageCost->execute());
    }
}
