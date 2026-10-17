<?php

namespace App\Clinical\UseCases;

use App\Clinical\Entities\Models\Appointment;
use App\Hospital\Entities\HospitalCalendar;
use Illuminate\Validation\ValidationException;

final class CancelAppointment
{
    public function execute(Appointment $appointment): void
    {
        if ($appointment->startDateTime->lessThanOrEqualTo(now())) {
            $startedAt = $appointment->startDateTime->setTimezone(HospitalCalendar::timezone())->format('Y-m-d H:i');

            throw ValidationException::withMessages([
                'appointment' => "This appointment started at {$startedAt} and cannot be cancelled.",
            ]);
        }

        $appointment->delete();
    }
}
