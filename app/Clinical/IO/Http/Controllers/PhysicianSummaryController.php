<?php

namespace App\Clinical\IO\Http\Controllers;

use App\Clinical\UseCases\ShowClinicalSummary;
use App\Staff\Entities\Models\Physician;
use Illuminate\Http\JsonResponse;

class PhysicianSummaryController
{
    public function show(Physician $physician, ShowClinicalSummary $showClinicalSummary): JsonResponse
    {
        return response()->json($showClinicalSummary->execute($physician));
    }
}
