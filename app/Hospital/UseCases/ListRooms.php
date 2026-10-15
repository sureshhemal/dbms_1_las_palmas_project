<?php

namespace App\Hospital\UseCases;

use App\Hospital\Entities\Models\Room;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\LengthAwarePaginator;

final class ListRooms
{
    public function execute(int $perPage, int $page, ?string $search = null, array $roomTypes = []): LengthAwarePaginator
    {
        return Room::query()
            ->when($search, fn (Builder $query) => $query->where('roomID', 'like', "%{$search}%"))
            ->when($roomTypes, fn (Builder $query) => $query->whereIn('roomType', $roomTypes))
            ->orderBy('roomID')
            ->paginate($perPage, ['*'], 'page', $page);
    }
}
