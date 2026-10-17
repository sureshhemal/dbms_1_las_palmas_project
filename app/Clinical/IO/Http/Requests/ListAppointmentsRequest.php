<?php

namespace App\Clinical\IO\Http\Requests;

use App\Clinical\Entities\AppointmentStatus;
use App\Clinical\IO\Http\DTOs\AppointmentFilters;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;

class ListAppointmentsRequest extends FormRequest
{
    public function getFilters(): AppointmentFilters
    {
        return new AppointmentFilters(
            search: $this->string('search')->trim()->toString() ?: null,
            years: collect($this->array('years'))
                ->filter(fn (mixed $year): bool => is_numeric($year))
                ->map(fn (mixed $year): int => (int) $year)
                ->values()
                ->all(),
            statuses: collect($this->array('statuses'))
                ->map(fn (mixed $status): ?AppointmentStatus => AppointmentStatus::tryFrom((string) $status))
                ->filter()
                ->values()
                ->all(),
            physicianIds: collect($this->array('physician_ids'))
                ->filter(fn (mixed $physicianId): bool => Str::isUlid($physicianId))
                ->values()
                ->all(),
        );
    }
}
