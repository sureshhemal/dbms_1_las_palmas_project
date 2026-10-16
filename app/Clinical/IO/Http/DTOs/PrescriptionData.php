<?php

namespace App\Clinical\IO\Http\DTOs;

use Carbon\CarbonImmutable;

final readonly class PrescriptionData
{
    public function __construct(
        public ?string $patientId,
        public ?string $medicationId,
        public ?string $physicianId,
        public ?CarbonImmutable $prescribedDate,
        public ?string $dose,
    ) {}
}
