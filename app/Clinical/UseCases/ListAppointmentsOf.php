<?php

namespace App\Clinical\UseCases;

use App\Clinical\Entities\Models\Appointment;
use App\Patient\Entities\Models\Patient;
use App\Staff\Entities\Models\Physician;
use Illuminate\Pagination\LengthAwarePaginator;

final class ListAppointmentsOf
{
    public function execute(Patient|Physician $subject, int $perPage, int $page): LengthAwarePaginator
    {
        return Appointment::query()
            ->with(ListAppointments::RELATIONS)
            ->whereBelongsTo($subject)
            ->orderByDesc('startDateTime')
            ->latest('id')
            ->paginate($perPage, ['*'], 'page', $page);
    }
}
