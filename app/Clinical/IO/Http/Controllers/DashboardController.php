<?php

namespace App\Clinical\IO\Http\Controllers;

use App\Clinical\UseCases\ShowTodayAtHospital;
use Illuminate\Http\JsonResponse;

class DashboardController
{
    public function show(ShowTodayAtHospital $showTodayAtHospital): JsonResponse
    {
        return response()->json($showTodayAtHospital->execute());
    }
}
