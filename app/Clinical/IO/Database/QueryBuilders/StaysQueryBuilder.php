<?php

namespace App\Clinical\IO\Database\QueryBuilders;

use App\Clinical\Entities\StayStatus;
use App\Hospital\Entities\HospitalCalendar;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Builder;

class StaysQueryBuilder extends Builder
{
    public function whereAdmitted(): self
    {
        $today = HospitalCalendar::today()->toDateString();

        return $this->where('startDate', '<=', $today)
            ->where(fn (Builder $notOver) => $notOver->whereNull('endDate')->orWhere('endDate', '>=', $today));
    }

    public function whereScheduled(): self
    {
        return $this->where('startDate', '>', HospitalCalendar::today()->toDateString());
    }

    public function whereDischarged(): self
    {
        return $this->where('endDate', '<', HospitalCalendar::today()->toDateString());
    }

    public function whereStatusIn(array $statuses): self
    {
        return $this->where(fn (Builder $query) => collect($statuses)
            ->each(function (StayStatus $status) use ($query) {
                return $query->orWhere(function (StaysQueryBuilder $stays) use ($status) {
                    return match ($status) {
                        StayStatus::Admitted => $stays->whereAdmitted(),
                        StayStatus::Scheduled => $stays->whereScheduled(),
                        StayStatus::Discharged => $stays->whereDischarged(),
                    };
                });
            },
            ));
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
