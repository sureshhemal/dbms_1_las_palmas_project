<?php

namespace App\Clinical\IO\Http\Requests;

use App\Clinical\IO\Http\DTOs\ExtensionData;
use Illuminate\Foundation\Http\FormRequest;

class ExtendStayRequest extends FormRequest
{
    use ResolvesClinicalRecords;

    public function getData(): ExtensionData
    {
        return new ExtensionData(
            stay: $this->stayInRoute(),
            endDate: $this->calendarDate('endDate'),
        );
    }
}
