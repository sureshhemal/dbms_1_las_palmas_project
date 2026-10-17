<?php

namespace App\Clinical\IO\Http\Requests;

use App\Clinical\IO\Http\DTOs\AppointmentData;
use App\Hospital\Entities\HospitalCalendar;
use App\Patient\Entities\Models\Patient;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Http\FormRequest;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class AppointmentRequest extends FormRequest
{
    use ResolvesClinicalRecords;

    public function getData(): AppointmentData
    {
        return new AppointmentData(
            patient: $this->patient(),
            physician: $this->physician(),
            nurse: $this->nurse(),
            startDateTime: $this->hospitalDateTime('startTime'),
            endDateTime: $this->filled('endTime') ? $this->hospitalDateTime('endTime') : null,
        );
    }

    private function patient(): Patient
    {
        $patient = Patient::query()->find($this->string('patient_id')->toString());

        return $patient instanceof Patient ? $patient : throw new NotFoundHttpException('Patient not found.');
    }

    private function hospitalDateTime(string $timeKey): CarbonImmutable
    {
        return CarbonImmutable::createFromFormat(
            '!Y-m-d H:i',
            "{$this->string('date')} {$this->string($timeKey)}",
            HospitalCalendar::timezone(),
        )->utc();
    }
}
