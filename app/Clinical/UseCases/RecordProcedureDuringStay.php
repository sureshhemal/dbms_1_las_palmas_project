<?php

namespace App\Clinical\UseCases;

use App\Clinical\Entities\Models\PerformedProcedure;
use App\Clinical\IO\Http\DTOs\StayProcedureData;

final class RecordProcedureDuringStay
{
    public function __construct(private EnsurePerformedProcedureIsAllowed $ensurePerformedProcedureIsAllowed) {}

    public function execute(StayProcedureData $data): PerformedProcedure
    {
        $this->ensurePerformedProcedureIsAllowed->execute($data->stay, $data->procedure);

        $performedProcedure = new PerformedProcedure([
            'patient_id' => $data->stay->patient_id,
            'stay_id' => $data->stay->id,
            'procedure_id' => $data->procedure->procedure->id,
            'physician_id' => $data->procedure->physician->id,
            'nurse_id' => $data->procedure->nurse->id,
            'procDate' => $data->procedure->procDate->toDateString(),
        ]);
        $performedProcedure->save();

        return $performedProcedure->load(ListPerformedProcedures::RELATIONS);
    }
}
