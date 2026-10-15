<?php

namespace App\Staff\UseCases;

use App\Staff\Entities\Models\Nurse;
use App\Staff\Entities\Models\OnCall;
use App\Staff\IO\Http\DTOs\OnCallData;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Validation\ValidationException;

final class EnsureOnCallDoesNotOverlap
{
    public function execute(OnCallData $data, ?OnCall $ignoredOnCall = null): void
    {
        if ($data->nurseId === null || $data->startDate === null || $data->endDate === null) {
            return;
        }

        $nurse = Nurse::query()->whereKey($data->nurseId)->lockForUpdate()->first();

        if ($nurse === null) {
            return;
        }

        $overlappingOnCall = OnCall::query()
            ->where('nurse_id', $nurse->id)
            ->when($ignoredOnCall, fn (Builder $query) => $query->whereKeyNot($ignoredOnCall?->id))
            ->whereOverlapping($data->startDate, $data->endDate)
            ->orderBy('startDate')
            ->first();

        if ($overlappingOnCall instanceof OnCall) {
            throw ValidationException::withMessages([
                'startDate' => "{$nurse->name} is already on call from {$overlappingOnCall->startDate->toDateString()} to {$overlappingOnCall->endDate->toDateString()}.",
            ]);
        }
    }
}
