<?php

namespace App\Clinical\IO\Http\DTOs;

use App\Clinical\Entities\Models\Stay;

final readonly class StayProcedureData
{
    public function __construct(
        public Stay $stay,
        public ProcedureDetails $procedure,
    ) {}
}
