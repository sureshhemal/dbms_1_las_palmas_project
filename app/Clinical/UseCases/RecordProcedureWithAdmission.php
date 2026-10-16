<?php

namespace App\Clinical\UseCases;

use App\Clinical\Entities\Models\PerformedProcedure;
use App\Clinical\IO\Http\DTOs\ProcedureEntryData;
use App\Clinical\IO\Http\DTOs\StayProcedureData;
use Illuminate\Support\Facades\DB;

final class RecordProcedureWithAdmission
{
    public function __construct(
        private AdmitPatient $admitPatient,
        private RecordProcedureDuringStay $recordProcedureDuringStay,
    ) {}

    public function execute(ProcedureEntryData $data): PerformedProcedure
    {
        return DB::transaction(fn (): PerformedProcedure => $this->recordProcedureDuringStay->execute(new StayProcedureData(
            stay: $this->admitPatient->execute($data->admission),
            procedure: $data->procedure,
        )));
    }
}
