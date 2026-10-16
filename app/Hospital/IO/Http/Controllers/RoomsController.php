<?php

namespace App\Hospital\IO\Http\Controllers;

use App\Hospital\Entities\Models\Room;
use App\Hospital\IO\Http\Requests\ListRoomsRequest;
use App\Hospital\IO\Http\Requests\RoomRequest;
use App\Hospital\UseCases\CreateRoom;
use App\Hospital\UseCases\DecommissionRoom;
use App\Hospital\UseCases\ListRooms;
use App\Hospital\UseCases\UpdateRoom;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;

class RoomsController
{
    public function index(ListRoomsRequest $request, ListRooms $listRooms): JsonResponse
    {
        return response()->json($listRooms->execute(
            perPage: $request->integer('per_page', 20),
            page: $request->integer('page', 1),
            filters: $request->getFilters(),
        ));
    }

    public function store(RoomRequest $request, CreateRoom $createRoom): JsonResponse
    {
        return response()->json($createRoom->execute($request->getData()), 201);
    }

    public function update(Room $room, RoomRequest $request, UpdateRoom $updateRoom): JsonResponse
    {
        return response()->json($updateRoom->execute($room, $request->getData()));
    }

    public function destroy(Room $room, DecommissionRoom $decommissionRoom): Response
    {
        $decommissionRoom->execute($room);

        return response()->noContent();
    }
}
