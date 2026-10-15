<?php

namespace Database\Seeders;

use App\Hospital\Entities\Models\Room;
use App\Hospital\Entities\RoomType;
use Illuminate\Database\Seeder;

class RoomSeeder extends Seeder
{
    private const int FLOORS = 5;

    private const int ROOMS_PER_FLOOR = 15;

    private const int SINGLE_ROOMS_PER_FLOOR = 9;

    public function run(): void
    {
        if (Room::query()->withTrashed()->exists()) {
            return;
        }

        foreach (range(1, self::FLOORS) as $floor) {
            foreach (range(1, self::ROOMS_PER_FLOOR) as $roomNumber) {
                Room::factory()
                    ->withType($roomNumber <= self::SINGLE_ROOMS_PER_FLOOR ? RoomType::Single : RoomType::Double)
                    ->create(['roomID' => $floor * 100 + $roomNumber]);
            }
        }
    }
}
