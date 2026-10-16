<?php

namespace App\Clinical\IO\Http\Controllers;

use App\Clinical\UseCases\ListPhysicianUpcomingProcedures;
use App\Staff\Entities\Models\Physician;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PhysicianUpcomingProceduresController
{
    public function index(Physician $physician, Request $request, ListPhysicianUpcomingProcedures $listPhysicianUpcomingProcedures): JsonResponse
    {
        return response()->json($listPhysicianUpcomingProcedures->execute(
            physician: $physician,
            perPage: $request->integer('per_page', 10),
            page: $request->integer('page', 1),
        ));
    }
}
