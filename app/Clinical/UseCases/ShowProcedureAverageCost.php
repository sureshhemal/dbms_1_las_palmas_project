<?php

namespace App\Clinical\UseCases;

use App\Clinical\Entities\Models\Procedure;
use App\Clinical\Entities\ProcedureAverageCost;

final class ShowProcedureAverageCost
{
    public function execute(): ProcedureAverageCost
    {
        $averageCost = Procedure::query()->avg('cost');

        return new ProcedureAverageCost(
            averageCost: $averageCost === null ? null : (float) $averageCost,
            pricedProcedureCount: Procedure::query()->count('cost'),
            unpricedProcedureCount: Procedure::query()->whereNull('cost')->count(),
        );
    }
}
