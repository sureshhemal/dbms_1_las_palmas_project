<?php

namespace App\Staff\IO\Http\Controllers;

use App\Staff\UseCases\CountStaff;
use Illuminate\Http\JsonResponse;

class StaffController
{
    public function index(CountStaff $countStaff): JsonResponse
    {
        return response()->json($countStaff->execute());
    }
}
