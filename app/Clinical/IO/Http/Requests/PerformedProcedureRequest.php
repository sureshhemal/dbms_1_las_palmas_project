<?php

namespace App\Clinical\IO\Http\Requests;

use App\Clinical\IO\Http\DTOs\AdmissionData;
use App\Clinical\IO\Http\DTOs\ProcedureEntryData;
use App\Hospital\Entities\Models\Room;
use App\Patient\Entities\Models\Patient;
use Illuminate\Foundation\Http\FormRequest;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class PerformedProcedureRequest extends FormRequest
{
    use ResolvesClinicalRecords;

    public function getData(): ProcedureEntryData
    {
        return new ProcedureEntryData(
            admission: new AdmissionData(
                patient: $this->patient(),
                room: $this->room(),
                startDate: $this->calendarDate('startDate'),
                endDate: $this->optionalCalendarDate('endDate'),
            ),
            procedure: $this->procedureDetails(),
        );
    }

    private function patient(): Patient
    {
        $patient = Patient::query()->find($this->string('patient_id')->toString());

        return $patient instanceof Patient ? $patient : throw new NotFoundHttpException('Patient not found.');
    }

    private function room(): Room
    {
        $room = Room::query()->find($this->string('room_id')->toString());

        return $room instanceof Room ? $room : throw new NotFoundHttpException('Room not found.');
    }
}
