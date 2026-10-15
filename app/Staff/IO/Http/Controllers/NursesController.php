<?php

namespace App\Staff\IO\Http\Controllers;

use App\Staff\Entities\Models\Nurse;
use App\Staff\Entities\NursePosition;
use App\Staff\IO\Http\Requests\NurseRequest;
use App\Staff\UseCases\CreateNurse;
use App\Staff\UseCases\ListNurses;
use App\Staff\UseCases\UpdateNurse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class NursesController
{
    public function index(Request $request, ListNurses $listNurses): JsonResponse
    {
        return response()->json($listNurses->execute(
            perPage: $request->integer('per_page', 20),
            page: $request->integer('page', 1),
            search: $request->string('search')->trim()->toString() ?: null,
            positions: collect($request->array('positions'))
                ->map(fn (mixed $position): ?NursePosition => NursePosition::tryFrom((string) $position))
                ->filter()
                ->values()
                ->all(),
        ));
    }

    public function store(NurseRequest $request, CreateNurse $createNurse): JsonResponse
    {
        return response()->json($createNurse->execute($request->getData()), 201);
    }

    public function update(Nurse $nurse, NurseRequest $request, UpdateNurse $updateNurse): JsonResponse
    {
        return response()->json($updateNurse->execute($nurse, $request->getData()));
    }
}
