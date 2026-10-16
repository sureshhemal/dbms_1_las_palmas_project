<?php

namespace App\Hospital\UseCases;

use App\Hospital\Entities\Models\Room;

final class DecommissionRoom
{
    public function execute(Room $room): void
    {
        $room->delete();
    }
}
