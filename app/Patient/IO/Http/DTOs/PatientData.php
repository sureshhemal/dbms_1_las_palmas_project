<?php

namespace App\Patient\IO\Http\DTOs;

use Carbon\CarbonImmutable;

final readonly class PatientData
{
    public function __construct(
        public ?int $patientID,
        public ?string $name,
        public ?string $ssn,
        public ?string $address,
        public ?CarbonImmutable $dob,
        public ?string $phone,
        public ?int $insuranceNumber,
        public ?string $primaryPhysicianId,
    ) {}
}
