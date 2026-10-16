<?php

namespace App\Clinical\IO\Http\Controllers;

use App\Clinical\UseCases\ListPhysicianPerformedProcedures;
use App\Staff\Entities\Models\Physician;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PhysicianPerformedProceduresController
{
    public function index(Physician $physician, Request $request, ListPhysicianPerformedProcedures $listPhysicianPerformedProcedures): JsonResponse
    {
        return response()->json($listPhysicianPerformedProcedures->execute(
            physician: $physician,
            perPage: $request->integer('per_page', 10),
            page: $request->integer('page', 1),
        ));
    }
}
