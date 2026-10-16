<?php

namespace App\Clinical\UseCases;

use App\Clinical\Entities\Models\Stay;
use App\Clinical\IO\Database\QueryBuilders\StaysQueryBuilder;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\LengthAwarePaginator;

final class ListStays
{
    public const array RELATIONS = [
        'patient:id,patientID,name,deleted_at',
        'room:id,roomID,roomType,deleted_at',
    ];

    public function execute(int $perPage, int $page, ?string $search = null, ?string $status = null): LengthAwarePaginator
    {
        return Stay::query()
            ->with(self::RELATIONS)
            ->when($search, fn (Builder $query) => $query->where(fn (Builder $matches) => $matches
                ->whereHas('patient', fn (Builder $patient) => $patient->whereAny(['name', 'patientID'], 'like', "%{$search}%"))
                ->orWhereHas('room', fn (Builder $room) => $room->where('roomID', 'like', "%{$search}%"))))
            ->when($status === 'current', fn (StaysQueryBuilder $query) => $query->whereCurrent())
            ->when($status === 'discharged', fn (StaysQueryBuilder $query) => $query->whereDischarged())
            ->orderByDesc('startDate')
            ->orderByDesc('stayID')
            ->paginate($perPage, ['*'], 'page', $page);
    }
}
