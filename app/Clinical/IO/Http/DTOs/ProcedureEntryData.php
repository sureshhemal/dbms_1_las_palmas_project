<?php

namespace App\Clinical\IO\Http\DTOs;

final readonly class ProcedureEntryData
{
    public function __construct(
        public AdmissionData $admission,
        public ProcedureDetails $procedure,
    ) {}
}
