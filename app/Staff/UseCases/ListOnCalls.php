<?php

namespace App\Staff\UseCases;

use App\Staff\Entities\Models\OnCall;
use App\Staff\IO\Database\QueryBuilders\OnCallsQueryBuilder;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

final class ListOnCalls
{
    public function execute(?CarbonInterface $from = null, ?CarbonInterface $to = null, array $nurseIds = []): Collection
    {
        return OnCall::query()
            ->with('nurse:id,nurseID,name,position')
            ->whereNurseIsOnStaff()
            ->when($from && $to, fn (OnCallsQueryBuilder $query) => $query->whereOverlapping($from, $to))
            ->when($nurseIds, fn (Builder $query) => $query->whereIn('nurse_id', $nurseIds))
            ->orderBy('startDate')
            ->orderBy('id')
            ->get();
    }
}
