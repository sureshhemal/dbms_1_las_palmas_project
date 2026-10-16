<?php

namespace App\Clinical\Entities;

use Illuminate\Support\Collection;
use stdClass;

final readonly class BedOccupancy
{
    public function __construct(private Collection $stays) {}

    public function bedsTakenOn(string $day): int
    {
        return $this->stays
            ->filter(fn (stdClass $stay): bool => $stay->startDate <= $day
                && ($stay->endDate === null || $stay->endDate > $day))
            ->count();
    }

    public function mostBedsTakenBetween(string $firstDay, ?string $leavingDay): int
    {
        return (int) $this->daysWhenPatientsArriveBetween($firstDay, $leavingDay)
            ->map(fn (string $arrivalDay): int => $this->bedsTakenOn($arrivalDay))
            ->max();
    }

    public function firstFullDayBetween(string $firstDay, ?string $leavingDay, int $beds): ?string
    {
        return $this->daysWhenPatientsArriveBetween($firstDay, $leavingDay)
            ->first(fn (string $arrivalDay): bool => $this->bedsTakenOn($arrivalDay) >= $beds);
    }

    private function daysWhenPatientsArriveBetween(string $firstDay, ?string $leavingDay): Collection
    {
        return $this->stays
            ->map(fn (stdClass $stay): string => $stay->startDate)
            ->push($firstDay)
            ->filter(fn (string $arrivalDay): bool => $arrivalDay >= $firstDay
                && ($leavingDay === null || $arrivalDay < $leavingDay))
            ->unique()
            ->sort()
            ->values();
    }
}
