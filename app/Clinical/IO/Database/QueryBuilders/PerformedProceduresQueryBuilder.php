<?php

namespace App\Clinical\IO\Database\QueryBuilders;

use App\Clinical\Entities\PerformedProcedureStatus;
use App\Hospital\Entities\HospitalCalendar;
use Illuminate\Database\Eloquent\Builder;

class PerformedProceduresQueryBuilder extends Builder
{
    public function wherePerformed(): self
    {
        return $this->where('procDate', '<=', HospitalCalendar::today()->toDateString());
    }

    public function whereScheduled(): self
    {
        return $this->where('procDate', '>', HospitalCalendar::today()->toDateString());
    }

    public function whereStatusIn(array $statuses): self
    {
        return $this->where(fn (Builder $query) => collect($statuses)->each(
            fn (PerformedProcedureStatus $status) => $query->orWhere(fn (PerformedProceduresQueryBuilder $procedures) => match ($status) {
                PerformedProcedureStatus::Performed => $procedures->wherePerformed(),
                PerformedProcedureStatus::Scheduled => $procedures->whereScheduled(),
            }),
        ));
    }
}
