<?php

namespace App\Clinical\UseCases;

use App\Clinical\Entities\BedOccupancy;
use App\Clinical\Entities\Models\Stay;
use App\Hospital\Entities\Models\Room;
use Carbon\CarbonImmutable;

final class ListRoomAvailability
{
    public function execute(CarbonImmutable $day, ?CarbonImmutable $leavingDay = null): array
    {
        $staysByRoom = Stay::query()
            ->whereOverlapping($day, $leavingDay)
            ->toBase()
            ->get(['room_id', 'startDate', 'endDate'])
            ->groupBy('room_id');

        return Room::query()
            ->orderBy('roomID')
            ->get(['id', 'roomID', 'roomType'])
            ->map(function (Room $room) use ($staysByRoom, $day, $leavingDay): array {
                $beds = $room->roomType->beds();
                $bedsTaken = new BedOccupancy($staysByRoom->get($room->id, collect()))
                    ->mostBedsTakenBetween($day->toDateString(), $leavingDay?->toDateString());

                return [
                    'id' => $room->id,
                    'roomID' => $room->roomID,
                    'roomType' => $room->roomType->value,
                    'beds' => $beds,
                    'bedsTaken' => $bedsTaken,
                    'bedsFree' => max(0, $beds - $bedsTaken),
                ];
            })
            ->filter(fn (array $room): bool => $room['bedsFree'] > 0)
            ->values()
            ->all();
    }
}
