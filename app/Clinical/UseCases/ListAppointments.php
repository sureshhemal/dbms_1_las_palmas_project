<?php

namespace App\Clinical\UseCases;

use App\Clinical\Entities\Models\Appointment;
use App\Clinical\IO\Database\QueryBuilders\AppointmentsQueryBuilder;
use App\Clinical\IO\Http\DTOs\AppointmentFilters;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\LengthAwarePaginator;

final class ListAppointments
{
    public const array RELATIONS = [
        'patient:id,patientID,name,deleted_at',
        'physician:id,physicianID,name,deleted_at',
        'nurse:id,nurseID,name,deleted_at',
    ];

    public function execute(int $perPage, int $page, AppointmentFilters $filters): LengthAwarePaginator
    {
        $search = $filters->search;

        return Appointment::query()
            ->with(self::RELATIONS)
            ->when($search, fn (Builder $query) => $query->whereHas('patient', fn (Builder $patient) => $patient->whereAny(['name', 'patientID'], 'like', "%{$search}%")))
            ->when($filters->years, fn (AppointmentsQueryBuilder $query) => $query->whereStartingInHospitalYears($filters->years))
            ->when($filters->statuses, fn (AppointmentsQueryBuilder $query) => $query->whereStatusIn($filters->statuses))
            ->when($filters->physicianIds, fn (Builder $query) => $query->whereIn('physician_id', $filters->physicianIds))
            ->orderByDesc('startDateTime')
            ->latest('id')
            ->paginate($perPage, ['*'], 'page', $page);
    }
}
