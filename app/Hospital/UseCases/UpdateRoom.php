<?php

namespace App\Hospital\UseCases;

use App\Hospital\Entities\Models\Room;
use App\Hospital\IO\Http\DTOs\RoomData;

final class UpdateRoom
{
    public function execute(Room $room, RoomData $data): Room
    {
        $room->update([
            'roomID' => $data->roomID ?? $room->roomID,
            'roomType' => $data->roomType,
        ]);

        return $room;
    }
}
