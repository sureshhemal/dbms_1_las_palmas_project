<?php

namespace App\Clinical\IO\Http\Controllers;

use App\Clinical\UseCases\ListPhysicianProcedures;
use App\Staff\Entities\Models\Physician;
use Illuminate\Http\JsonResponse;

class PhysicianProceduresController
{
    public function index(Physician $physician, ListPhysicianProcedures $listPhysicianProcedures): JsonResponse
    {
        return response()->json($listPhysicianProcedures->execute($physician));
    }
}
