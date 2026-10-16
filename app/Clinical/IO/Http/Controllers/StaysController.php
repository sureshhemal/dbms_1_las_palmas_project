<?php

namespace App\Clinical\IO\Http\Controllers;

use App\Clinical\IO\Http\Requests\AdmissionRequest;
use App\Clinical\IO\Http\Requests\DischargeRequest;
use App\Clinical\UseCases\AdmitPatient;
use App\Clinical\UseCases\DischargePatient;
use App\Clinical\UseCases\ListStays;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class StaysController
{
    public function index(Request $request, ListStays $listStays): JsonResponse
    {
        return response()->json($listStays->execute(
            perPage: $request->integer('per_page', 20),
            page: $request->integer('page', 1),
            search: $request->string('search')->trim()->toString() ?: null,
            status: $request->string('status')->toString() ?: null,
        ));
    }

    public function store(AdmissionRequest $request, AdmitPatient $admitPatient): JsonResponse
    {
        return response()->json($admitPatient->execute($request->getData()), 201);
    }

    public function discharge(DischargeRequest $request, DischargePatient $dischargePatient): JsonResponse
    {
        return response()->json($dischargePatient->execute($request->getData()));
    }
}
