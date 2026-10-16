<?php

namespace App\Clinical\IO\Http\Controllers;

use App\Clinical\UseCases\ListPerformedProcedureYears;
use Illuminate\Http\JsonResponse;

class PerformedProcedureYearsController
{
    public function index(ListPerformedProcedureYears $listPerformedProcedureYears): JsonResponse
    {
        return response()->json($listPerformedProcedureYears->execute());
    }
}
