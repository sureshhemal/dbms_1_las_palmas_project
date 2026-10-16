<?php

namespace App\Clinical\IO\Http\Controllers;

use App\Clinical\IO\Http\Requests\PrescriptionRequest;
use App\Clinical\UseCases\CreatePrescription;
use App\Clinical\UseCases\ListPrescriptions;
use App\Clinical\UseCases\ListPrescriptionYears;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PrescriptionsController
{
    public function index(Request $request, ListPrescriptions $listPrescriptions): JsonResponse
    {
        return response()->json($listPrescriptions->execute(
            perPage: $request->integer('per_page', 20),
            page: $request->integer('page', 1),
            search: $request->string('search')->trim()->toString() ?: null,
            years: collect($request->array('years'))
                ->filter(fn (mixed $year): bool => is_numeric($year))
                ->map(fn (mixed $year): int => (int) $year)
                ->values()
                ->all(),
        ));
    }

    public function years(ListPrescriptionYears $listPrescriptionYears): JsonResponse
    {
        return response()->json($listPrescriptionYears->execute());
    }

    public function store(PrescriptionRequest $request, CreatePrescription $createPrescription): JsonResponse
    {
        return response()->json($createPrescription->execute($request->getData()), 201);
    }
}
