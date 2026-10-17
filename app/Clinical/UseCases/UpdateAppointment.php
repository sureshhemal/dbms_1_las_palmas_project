<?php

namespace App\Clinical\UseCases;

use App\Clinical\Entities\Models\Appointment;
use App\Clinical\IO\Http\DTOs\AppointmentData;

final class UpdateAppointment
{
    public function execute(Appointment $appointment, AppointmentData $data): Appointment
    {
        $appointment->update([
            'patient_id' => $data->patient->id,
            'physician_id' => $data->physician->id,
            'nurse_id' => $data->nurse->id,
            'startDateTime' => $data->startDateTime->toDateTimeString(),
            'endDateTime' => $data->endDateTime?->toDateTimeString(),
        ]);

        return $appointment->load(ListAppointments::RELATIONS);
    }
}
