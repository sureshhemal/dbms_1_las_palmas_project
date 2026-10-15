<?php

namespace App\Clinical\UseCases;

use App\Clinical\Entities\Models\Procedure;
use App\Clinical\IO\Http\DTOs\ProcedureData;

final class UpdateProcedure
{
    public function execute(Procedure $procedure, ProcedureData $data): Procedure
    {
        $procedure->update([
            'procID' => $data->procID ?? $procedure->procID,
            'name' => $data->name,
            'cost' => $data->cost,
        ]);

        return $procedure;
    }
}
