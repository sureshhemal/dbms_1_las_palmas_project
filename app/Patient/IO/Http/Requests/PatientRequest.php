<?php

namespace App\Patient\IO\Http\Requests;

use App\Patient\IO\Http\DTOs\PatientData;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Http\FormRequest;

class PatientRequest extends FormRequest
{
    public function getData(): PatientData
    {
        return new PatientData(
            patientID: $this->filled('patientID') ? $this->integer('patientID') : null,
            name: $this->optionalText('name'),
            ssn: $this->optionalText('ssn'),
            address: $this->optionalText('address'),
            dob: $this->filled('dob') ? CarbonImmutable::createFromFormat('!Y-m-d', $this->string('dob')->toString()) : null,
            phone: $this->optionalText('phone'),
            insuranceNumber: $this->filled('insuranceNumber') ? $this->integer('insuranceNumber') : null,
            primaryPhysicianId: $this->optionalText('primary_physician_id'),
        );
    }

    private function optionalText(string $key): ?string
    {
        return $this->filled($key) ? $this->string($key)->trim()->toString() : null;
    }
}
