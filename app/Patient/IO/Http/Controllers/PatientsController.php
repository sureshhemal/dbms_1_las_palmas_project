<?php

namespace App\Patient\IO\Http\Controllers;

use App\Patient\Entities\Models\Patient;
use App\Patient\IO\Http\Requests\PatientRequest;
use App\Patient\UseCases\CreatePatient;
use App\Patient\UseCases\ListPatients;
use App\Patient\UseCases\RemovePatient;
use App\Patient\UseCases\UpdatePatient;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Str;

class PatientsController
{
    public function index(Request $request, ListPatients $listPatients): JsonResponse
    {
        return response()->json($listPatients->execute(
            perPage: $request->integer('per_page', 20),
            page: $request->integer('page', 1),
            search: $request->string('search')->trim()->toString() ?: null,
            primaryPhysicianIds: collect($request->array('primary_physician_ids'))
                ->filter(fn (mixed $physicianId): bool => Str::isUlid($physicianId))
                ->values()
                ->all(),
        ));
    }

    public function store(PatientRequest $request, CreatePatient $createPatient): JsonResponse
    {
        return response()->json($createPatient->execute($request->getData()), 201);
    }

    public function update(Patient $patient, PatientRequest $request, UpdatePatient $updatePatient): JsonResponse
    {
        return response()->json($updatePatient->execute($patient, $request->getData()));
    }

    public function destroy(Patient $patient, RemovePatient $removePatient): Response
    {
        $removePatient->execute($patient);

        return response()->noContent();
    }
}
