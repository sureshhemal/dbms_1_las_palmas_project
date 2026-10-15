<?php

namespace App\Hospital\UseCases;

use App\Hospital\Entities\Models\Room;

final class RemoveRoom
{
    public function execute(Room $room): void
    {
        $room->delete();
    }
}
