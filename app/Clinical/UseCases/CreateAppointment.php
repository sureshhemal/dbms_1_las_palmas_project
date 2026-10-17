<?php

namespace App\Clinical\UseCases;

use App\Clinical\Entities\Models\Appointment;
use App\Clinical\IO\Http\DTOs\AppointmentData;

final class CreateAppointment
{
    public function execute(AppointmentData $data): Appointment
    {
        $appointment = new Appointment([
            'patient_id' => $data->patient->id,
            'physician_id' => $data->physician->id,
            'nurse_id' => $data->nurse->id,
            'startDateTime' => $data->startDateTime->toDateTimeString(),
            'endDateTime' => $data->endDateTime?->toDateTimeString(),
        ]);
        $appointment->save();

        return $appointment->load(ListAppointments::RELATIONS);
    }
}
