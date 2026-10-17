<?php

namespace App\Clinical\IO\Http\Controllers;

use App\Clinical\UseCases\ListAppointmentsOf;
use App\Staff\Entities\Models\Physician;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PhysicianAppointmentsController
{
    public function index(Physician $physician, Request $request, ListAppointmentsOf $listAppointmentsOf): JsonResponse
    {
        return response()->json($listAppointmentsOf->execute(
            subject: $physician,
            perPage: $request->integer('per_page', 10),
            page: $request->integer('page', 1),
        ));
    }
}
