<?php

namespace App\Clinical\IO\Http\Requests;

use App\Clinical\IO\Http\DTOs\StayProcedureData;
use Illuminate\Foundation\Http\FormRequest;

class StayProcedureRequest extends FormRequest
{
    use ResolvesClinicalRecords;

    public function getData(): StayProcedureData
    {
        return new StayProcedureData(
            stay: $this->stayInRoute(),
            procedure: $this->procedureDetails(),
        );
    }
}
