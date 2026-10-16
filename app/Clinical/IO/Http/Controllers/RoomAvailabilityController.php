<?php

namespace App\Clinical\IO\Http\Controllers;

use App\Clinical\UseCases\ListRoomAvailability;
use App\Hospital\Entities\HospitalCalendar;
use Carbon\CarbonImmutable;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class RoomAvailabilityController
{
    public function index(Request $request, ListRoomAvailability $listRoomAvailability): JsonResponse
    {
        return response()->json($listRoomAvailability->execute(
            $request->filled('date')
                ? CarbonImmutable::createFromFormat('!Y-m-d', $request->string('date')->toString())
                : HospitalCalendar::today(),
            $request->filled('until')
                ? CarbonImmutable::createFromFormat('!Y-m-d', $request->string('until')->toString())
                : null,
        ));
    }
}
