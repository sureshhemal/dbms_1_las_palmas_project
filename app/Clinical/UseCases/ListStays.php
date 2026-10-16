<?php

namespace App\Clinical\UseCases;

use App\Clinical\Entities\Models\Stay;
use App\Clinical\IO\Database\QueryBuilders\StaysQueryBuilder;
use App\Clinical\IO\Http\DTOs\StayFilters;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\LengthAwarePaginator;

final class ListStays
{
    public const array RELATIONS = [
        'patient:id,patientID,name,deleted_at',
        'room:id,roomID,roomType,deleted_at',
    ];

    public function execute(int $perPage, int $page, StayFilters $filters): LengthAwarePaginator
    {
        $search = $filters->search;

        return Stay::query()
            ->with(self::RELATIONS)
            ->when($search, fn (Builder $query) => $query->where(fn (Builder $matches) => $matches
                ->where('stayID', 'like', "%{$search}%")
                ->orWhereHas('patient', fn (Builder $patient) => $patient->whereAny(['name', 'patientID'], 'like', "%{$search}%"))
                ->orWhereHas('room', fn (Builder $room) => $room->where('roomID', 'like', "%{$search}%"))))
            ->when($filters->statuses, fn (StaysQueryBuilder $query) => $query->whereStatusIn($filters->statuses))
            ->orderByDesc('startDate')
            ->orderByDesc('stayID')
            ->paginate($perPage, ['*'], 'page', $page);
    }
}
