<?php

namespace App\Hospital\IO\Http\Controllers;

use App\Hospital\Entities\Models\Room;
use App\Hospital\Entities\RoomType;
use App\Hospital\IO\Http\Requests\RoomRequest;
use App\Hospital\UseCases\CreateRoom;
use App\Hospital\UseCases\ListRooms;
use App\Hospital\UseCases\RemoveRoom;
use App\Hospital\UseCases\UpdateRoom;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class RoomsController
{
    public function index(Request $request, ListRooms $listRooms): JsonResponse
    {
        return response()->json($listRooms->execute(
            perPage: $request->integer('per_page', 20),
            page: $request->integer('page', 1),
            search: $request->string('search')->trim()->toString() ?: null,
            roomTypes: collect($request->array('room_types'))
                ->map(fn (mixed $roomType): ?RoomType => RoomType::tryFrom((string) $roomType))
                ->filter()
                ->values()
                ->all(),
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

    public function destroy(Room $room, RemoveRoom $removeRoom): Response
    {
        $removeRoom->execute($room);

        return response()->noContent();
    }
}
