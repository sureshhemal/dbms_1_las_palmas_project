<?php

namespace App\Clinical\IO\Http\Requests;

use App\Clinical\IO\Http\DTOs\PrescriptionData;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Http\FormRequest;

class PrescriptionRequest extends FormRequest
{
    public function getData(): PrescriptionData
    {
        return new PrescriptionData(
            patientId: $this->optionalText('patient_id'),
            medicationId: $this->optionalText('medication_id'),
            physicianId: $this->optionalText('physician_id'),
            prescribedDate: $this->filled('prescribedDate')
                ? CarbonImmutable::createFromFormat('!Y-m-d', $this->string('prescribedDate')->toString())
                : null,
            dose: $this->optionalText('dose'),
        );
    }

    private function optionalText(string $key): ?string
    {
        return $this->filled($key) ? $this->string($key)->trim()->toString() : null;
    }
}
