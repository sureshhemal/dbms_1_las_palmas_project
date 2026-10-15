<?php

namespace App\Staff\IO\Http\Requests;

use App\Staff\IO\Http\DTOs\OnCallData;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Http\FormRequest;

class OnCallRequest extends FormRequest
{
    public function getData(): OnCallData
    {
        return new OnCallData(
            nurseId: $this->filled('nurse_id') ? $this->string('nurse_id')->toString() : null,
            startDate: $this->calendarDate('startDate'),
            endDate: $this->calendarDate('endDate'),
        );
    }

    private function calendarDate(string $key): ?CarbonImmutable
    {
        return $this->filled($key)
            ? CarbonImmutable::createFromFormat('!Y-m-d', $this->string($key)->toString())
            : null;
    }
}
