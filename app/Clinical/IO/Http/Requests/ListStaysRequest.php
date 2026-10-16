<?php

namespace App\Clinical\IO\Http\Requests;

use App\Clinical\Entities\StayStatus;
use App\Clinical\IO\Http\DTOs\StayFilters;
use Illuminate\Foundation\Http\FormRequest;

class ListStaysRequest extends FormRequest
{
    public function getFilters(): StayFilters
    {
        return new StayFilters(
            search: $this->string('search')->trim()->toString() ?: null,
            statuses: collect($this->array('statuses'))
                ->map(fn (mixed $status): ?StayStatus => StayStatus::tryFrom((string) $status))
                ->filter()
                ->values()
                ->all(),
        );
    }
}
