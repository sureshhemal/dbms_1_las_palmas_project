<?php

namespace App\Clinical\IO\Database\QueryBuilders;

use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Builder;

class StaysQueryBuilder extends Builder
{
    public function whereCurrent(): self
    {
        return $this->whereNull('endDate');
    }

    public function whereDischarged(): self
    {
        return $this->whereNotNull('endDate');
    }

    public function whereStillAdmittedOnOrAfter(CarbonInterface $day): self
    {
        return $this->where(function (Builder $query) use ($day) {
            return $query->whereNull('endDate')
                ->orWhere('endDate', '>', $day->toDateString());
        });
    }

    public function whereOverlapping(CarbonInterface $startDate, ?CarbonInterface $endDate): self
    {
        return $this->whereStillAdmittedOnOrAfter($startDate)
            ->when($endDate, fn (Builder $query) => $query->where('startDate', '<', $endDate?->toDateString()));
    }

    public function whereOccupyingABedOn(CarbonInterface $day): self
    {
        return $this->where('startDate', '<=', $day->toDateString())
            ->whereStillAdmittedOnOrAfter($day);
    }
}
