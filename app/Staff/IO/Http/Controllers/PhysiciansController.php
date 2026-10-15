<?php

namespace App\Staff\IO\Http\Controllers;

use App\Staff\Entities\Models\Physician;
use App\Staff\Entities\PhysicianPosition;
use App\Staff\IO\Http\Requests\PhysicianRequest;
use App\Staff\UseCases\CreatePhysician;
use App\Staff\UseCases\ListPhysicians;
use App\Staff\UseCases\OffboardPhysician;
use App\Staff\UseCases\UpdatePhysician;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class PhysiciansController
{
    public function index(Request $request, ListPhysicians $listPhysicians): JsonResponse
    {
        return response()->json($listPhysicians->execute(
            search: $request->string('search')->trim()->toString() ?: null,
            positions: collect($request->array('positions'))
                ->map(fn (mixed $position): ?PhysicianPosition => PhysicianPosition::tryFrom((string) $position))
                ->filter()
                ->values()
                ->all(),
        ));
    }

    public function store(PhysicianRequest $request, CreatePhysician $createPhysician): JsonResponse
    {
        return response()->json($createPhysician->execute($request->getData()), 201);
    }

    public function update(Physician $physician, PhysicianRequest $request, UpdatePhysician $updatePhysician): JsonResponse
    {
        return response()->json($updatePhysician->execute($physician, $request->getData()));
    }

    public function destroy(Physician $physician, OffboardPhysician $offboardPhysician): Response
    {
        $offboardPhysician->execute($physician);

        return response()->noContent();
    }
}
