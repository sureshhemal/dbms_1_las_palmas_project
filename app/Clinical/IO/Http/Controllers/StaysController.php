<?php

namespace App\Clinical\IO\Http\Controllers;

use App\Clinical\IO\Http\Requests\DischargeRequest;
use App\Clinical\IO\Http\Requests\ExtendStayRequest;
use App\Clinical\IO\Http\Requests\ListStaysRequest;
use App\Clinical\IO\Http\Requests\StayProcedureRequest;
use App\Clinical\UseCases\DischargePatient;
use App\Clinical\UseCases\ExtendStay;
use App\Clinical\UseCases\ListStays;
use App\Clinical\UseCases\RecordProcedureDuringStay;
use Illuminate\Http\JsonResponse;

class StaysController
{
    public function index(ListStaysRequest $request, ListStays $listStays): JsonResponse
    {
        return response()->json($listStays->execute(
            perPage: $request->integer('per_page', 20),
            page: $request->integer('page', 1),
            filters: $request->getFilters(),
        ));
    }

    public function discharge(DischargeRequest $request, DischargePatient $dischargePatient): JsonResponse
    {
        return response()->json($dischargePatient->execute($request->getData()));
    }

    public function extend(ExtendStayRequest $request, ExtendStay $extendStay): JsonResponse
    {
        return response()->json($extendStay->execute($request->getData()));
    }

    public function recordProcedure(StayProcedureRequest $request, RecordProcedureDuringStay $recordProcedureDuringStay): JsonResponse
    {
        return response()->json($recordProcedureDuringStay->execute($request->getData()), 201);
    }
}
