<?php

namespace App\Clinical\UseCases;

use App\Clinical\Entities\Models\Procedure;

final class RemoveProcedure
{
    public function execute(Procedure $procedure): void
    {
        $procedure->delete();
    }
}
