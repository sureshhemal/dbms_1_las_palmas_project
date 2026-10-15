<?php

namespace App\Staff\IO\Database\QueryBuilders;

use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Builder;

class OnCallsQueryBuilder extends Builder
{
    public function whereOverlapping(CarbonInterface $startDate, CarbonInterface $endDate): self
    {
        return $this->where('startDate', '<=', $endDate->toDateString())
            ->where('endDate', '>=', $startDate->toDateString());
    }

    public function whereOnCallOn(CarbonInterface $day): self
    {
        return $this->where('startDate', '<=', $day->toDateString())
            ->where('endDate', '>=', $day->toDateString());
    }

    public function whereStartingOnOrAfter(CarbonInterface $day): self
    {
        return $this->where('startDate', '>=', $day->toDateString());
    }

    public function whereNurseIsOnStaff(): self
    {
        return $this->whereHas('nurse');
    }
}
