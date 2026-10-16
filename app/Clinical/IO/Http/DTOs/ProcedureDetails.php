<?php

namespace App\Clinical\IO\Http\DTOs;

use App\Clinical\Entities\Models\Procedure;
use App\Staff\Entities\Models\Nurse;
use App\Staff\Entities\Models\Physician;
use Carbon\CarbonImmutable;

final readonly class ProcedureDetails
{
    public function __construct(
        public Procedure $procedure,
        public Physician $physician,
        public Nurse $nurse,
        public CarbonImmutable $procDate,
    ) {}
}
