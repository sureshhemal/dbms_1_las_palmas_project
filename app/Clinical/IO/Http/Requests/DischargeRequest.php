<?php

namespace App\Clinical\IO\Http\Requests;

use App\Clinical\IO\Http\DTOs\DischargeData;
use Illuminate\Foundation\Http\FormRequest;

class DischargeRequest extends FormRequest
{
    use ResolvesClinicalRecords;

    public function getData(): DischargeData
    {
        return new DischargeData(
            stay: $this->stayInRoute(),
            endDate: $this->calendarDate('endDate'),
        );
    }
}
