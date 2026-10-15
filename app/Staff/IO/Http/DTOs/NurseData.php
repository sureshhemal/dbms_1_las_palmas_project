<?php

namespace App\Staff\IO\Http\DTOs;

use App\Staff\Entities\NursePosition;

final readonly class NurseData
{
    public function __construct(
        public ?int $nurseID,
        public string $name,
        public NursePosition $position,
        public ?string $ssn,
    ) {}
}
