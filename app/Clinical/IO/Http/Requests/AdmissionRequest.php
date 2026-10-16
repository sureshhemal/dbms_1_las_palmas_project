<?php

namespace App\Clinical\IO\Http\Requests;

use App\Clinical\IO\Http\DTOs\AdmissionData;
use App\Hospital\Entities\Models\Room;
use App\Patient\Entities\Models\Patient;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Http\FormRequest;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class AdmissionRequest extends FormRequest
{
    public function getData(): AdmissionData
    {
        return new AdmissionData(
            patient: $this->patient(),
            room: $this->room(),
            startDate: CarbonImmutable::createFromFormat('!Y-m-d', $this->string('startDate')->toString()),
            endDate: $this->filled('endDate')
                ? CarbonImmutable::createFromFormat('!Y-m-d', $this->string('endDate')->toString())
                : null,
        );
    }

    private function patient(): Patient
    {
        $patient = Patient::query()->find($this->string('patient_id')->toString());

        if (! $patient instanceof Patient) {
            throw new NotFoundHttpException('Patient not found.');
        }

        return $patient;
    }

    private function room(): Room
    {
        $room = Room::query()->find($this->string('room_id')->toString());

        if (! $room instanceof Room) {
            throw new NotFoundHttpException('Room not found.');
        }

        return $room;
    }
}
