<?php

namespace App\Clinical\IO\Database\QueryBuilders;

use App\Clinical\Entities\AppointmentStatus;
use App\Hospital\Entities\HospitalCalendar;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Builder;

class AppointmentsQueryBuilder extends Builder
{
    public function whereUpcoming(): self
    {
        return $this->where('startDateTime', '>', now()->toDateTimeString());
    }

    public function whereInProgress(): self
    {
        return $this->where('startDateTime', '<=', now()->toDateTimeString())
            ->where('endDateTime', '>', now()->toDateTimeString());
    }

    public function whereCompleted(): self
    {
        return $this->where('endDateTime', '<=', now()->toDateTimeString());
    }

    public function whereStatusIn(array $statuses): self
    {
        return $this->where(fn (Builder $query) => collect($statuses)->each(
            fn (AppointmentStatus $status) => $query->orWhere(fn (AppointmentsQueryBuilder $appointments) => match ($status) {
                AppointmentStatus::Upcoming => $appointments->whereUpcoming(),
                AppointmentStatus::InProgress => $appointments->whereInProgress(),
                AppointmentStatus::Completed => $appointments->whereCompleted(),
            }),
        ));
    }

    public function whereStartingInHospitalYears(array $years): self
    {
        return $this->where(fn (Builder $query) => collect($years)->each(
            fn (int $year) => $query->orWhere(fn (Builder $inYear) => $inYear
                ->where('startDateTime', '>=', self::startOfHospitalYear($year))
                ->where('startDateTime', '<', self::startOfHospitalYear($year + 1))),
        ));
    }

    private static function startOfHospitalYear(int $year): string
    {
        return CarbonImmutable::create($year, 1, 1, 0, 0, 0, HospitalCalendar::timezone())->utc()->toDateTimeString();
    }
}
