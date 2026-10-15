<?php

namespace App\Clinical\UseCases;

use App\Clinical\Entities\Models\Procedure;
use App\Clinical\IO\Http\DTOs\ProcedureData;

final class CreateProcedure
{
    public function execute(ProcedureData $data): Procedure
    {
        $procedure = new Procedure([
            'name' => $data->name,
            'cost' => $data->cost,
        ]);

        if ($data->procID !== null) {
            $procedure->procID = $data->procID;
        }

        $procedure->save();

        return $procedure;
    }
}
