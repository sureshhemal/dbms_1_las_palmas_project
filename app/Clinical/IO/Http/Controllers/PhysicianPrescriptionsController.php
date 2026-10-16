<?php

namespace App\Clinical\IO\Http\Controllers;

use App\Clinical\UseCases\ListPhysicianPrescriptions;
use App\Staff\Entities\Models\Physician;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PhysicianPrescriptionsController
{
    public function index(Physician $physician, Request $request, ListPhysicianPrescriptions $listPhysicianPrescriptions): JsonResponse
    {
        return response()->json($listPhysicianPrescriptions->execute(
            physician: $physician,
            perPage: $request->integer('per_page', 10),
            page: $request->integer('page', 1),
        ));
    }
}
