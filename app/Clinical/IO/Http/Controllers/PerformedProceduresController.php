<?php

namespace App\Clinical\IO\Http\Controllers;

use App\Clinical\IO\Http\Requests\ListPerformedProceduresRequest;
use App\Clinical\IO\Http\Requests\PerformedProcedureRequest;
use App\Clinical\UseCases\ListPerformedProcedures;
use App\Clinical\UseCases\RecordProcedureWithAdmission;
use Illuminate\Http\JsonResponse;

class PerformedProceduresController
{
    public function index(ListPerformedProceduresRequest $request, ListPerformedProcedures $listPerformedProcedures): JsonResponse
    {
        return response()->json($listPerformedProcedures->execute(
            perPage: $request->integer('per_page', 20),
            page: $request->integer('page', 1),
            filters: $request->getFilters(),
        ));
    }

    public function store(PerformedProcedureRequest $request, RecordProcedureWithAdmission $recordProcedureWithAdmission): JsonResponse
    {
        return response()->json($recordProcedureWithAdmission->execute($request->getData()), 201);
    }
}
