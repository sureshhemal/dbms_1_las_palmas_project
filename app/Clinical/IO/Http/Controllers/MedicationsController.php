<?php

namespace App\Clinical\IO\Http\Controllers;

use App\Clinical\Entities\Models\Medication;
use App\Clinical\IO\Http\Requests\MedicationRequest;
use App\Clinical\UseCases\CreateMedication;
use App\Clinical\UseCases\ListMedications;
use App\Clinical\UseCases\RemoveMedication;
use App\Clinical\UseCases\UpdateMedication;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class MedicationsController
{
    public function index(Request $request, ListMedications $listMedications): JsonResponse
    {
        return response()->json($listMedications->execute(
            perPage: $request->integer('per_page', 20),
            page: $request->integer('page', 1),
            search: $request->string('search')->trim()->toString() ?: null,
        ));
    }

    public function store(MedicationRequest $request, CreateMedication $createMedication): JsonResponse
    {
        return response()->json($createMedication->execute($request->getData()), 201);
    }

    public function update(Medication $medication, MedicationRequest $request, UpdateMedication $updateMedication): JsonResponse
    {
        return response()->json($updateMedication->execute($medication, $request->getData()));
    }

    public function destroy(Medication $medication, RemoveMedication $removeMedication): Response
    {
        $removeMedication->execute($medication);

        return response()->noContent();
    }
}
