<?php

namespace App\Clinical\IO\Http\Controllers;

use App\Clinical\Entities\Models\Procedure;
use App\Clinical\IO\Http\Requests\ProcedureRequest;
use App\Clinical\UseCases\CreateProcedure;
use App\Clinical\UseCases\ListProcedures;
use App\Clinical\UseCases\RemoveProcedure;
use App\Clinical\UseCases\UpdateProcedure;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class ProceduresController
{
    public function index(Request $request, ListProcedures $listProcedures): JsonResponse
    {
        return response()->json($listProcedures->execute(
            perPage: $request->integer('per_page', 20),
            page: $request->integer('page', 1),
            search: $request->string('search')->trim()->toString() ?: null,
        ));
    }

    public function store(ProcedureRequest $request, CreateProcedure $createProcedure): JsonResponse
    {
        return response()->json($createProcedure->execute($request->getData()), 201);
    }

    public function update(Procedure $procedure, ProcedureRequest $request, UpdateProcedure $updateProcedure): JsonResponse
    {
        return response()->json($updateProcedure->execute($procedure, $request->getData()));
    }

    public function destroy(Procedure $procedure, RemoveProcedure $removeProcedure): Response
    {
        $removeProcedure->execute($procedure);

        return response()->noContent();
    }
}
