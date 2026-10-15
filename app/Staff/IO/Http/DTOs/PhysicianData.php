<?php

namespace App\Staff\IO\Http\DTOs;

use App\Staff\Entities\PhysicianPosition;

final readonly class PhysicianData
{
    public function __construct(
        public ?int $physicianID,
        public string $name,
        public PhysicianPosition $position,
        public ?string $ssn,
        public array $departmentIds = [],
    ) {}
}
