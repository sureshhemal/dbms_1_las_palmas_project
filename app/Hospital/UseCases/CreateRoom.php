<?php

namespace App\Hospital\UseCases;

use App\Hospital\Entities\Models\Room;
use App\Hospital\IO\Http\DTOs\RoomData;

final class CreateRoom
{
    public function execute(RoomData $data): Room
    {
        $room = new Room(['roomType' => $data->roomType]);

        if ($data->roomID !== null) {
            $room->roomID = $data->roomID;
        }

        $room->save();

        return $room;
    }
}
