<?php

namespace App\Hospital\UseCases;

use App\Clinical\Entities\Models\Stay;
use App\Clinical\IO\Database\QueryBuilders\StaysQueryBuilder;
use App\Hospital\Entities\HospitalCalendar;
use App\Hospital\Entities\Models\Room;
use App\Hospital\Entities\RoomType;
use App\Hospital\IO\Http\DTOs\RoomsFilters;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\LengthAwarePaginator;

final class ListRooms
{
    public function execute(int $perPage, int $page, RoomsFilters $filters): LengthAwarePaginator
    {
        $today = HospitalCalendar::today();

        return Room::query()
            ->with([
                'currentStays:id,room_id,patient_id,startDate',
                'currentStays.patient:id,name,deleted_at',
            ])
            ->withCount(['stays as bedsTaken' => fn (StaysQueryBuilder $stays) => $stays->whereOccupyingABedOn($today)])
            ->when($filters->search, fn (Builder $query) => $query->where('roomID', 'like', "%{$filters->search}%"))
            ->when($filters->roomTypes, fn (Builder $query) => $query->whereIn('roomType', $filters->roomTypes))
            ->when($filters->bedsFree, function (Builder $query) use ($today, $filters): void {
                $this->whereBedsFreeOn($query, $today, $filters->bedsFree);
            })
            ->orderBy('roomID')
            ->paginate($perPage, ['*'], 'page', $page)
            ->through(fn (Room $room): Room => $room
                ->setAttribute('bedsFree', max(0, $room->roomType->beds() - (int) $room->getAttribute('bedsTaken')))
                ->makeHidden('bedsTaken'));
    }

    private function whereBedsFreeOn(Builder $query, CarbonInterface $day, array $bedsFreeCounts): void
    {
        $query->where(function (Builder $rooms) use ($day, $bedsFreeCounts): void {
            foreach (RoomType::cases() as $roomType) {
                foreach ($bedsFreeCounts as $bedsFree) {
                    $bedsTaken = $roomType->beds() - $bedsFree;

                    // 1 (single room) - 2 (requested beds) -> skipped
                    if ($bedsTaken < 0) {
                        continue;
                    }

                    $roomsInUse = Stay::query()->select('room_id')->whereOccupyingABedOn($day);

                    $rooms->orWhere(fn (Builder $room) => $bedsTaken === 0
                        ? $room->where('roomType', $roomType)->whereNotIn('id', $roomsInUse)
                        : $room->where('roomType', $roomType)
                            ->whereIn(
                                'id',
                                $roomsInUse->groupBy('room_id')
                                    ->havingRaw('count(*) = ?', [$bedsTaken])
                            )
                    );
                }
            }
        });
    }
}
