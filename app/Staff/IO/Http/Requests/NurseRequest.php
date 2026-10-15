<?php

namespace App\Staff\IO\Http\Requests;

use App\Staff\Entities\NursePosition;
use App\Staff\IO\Http\DTOs\NurseData;
use Illuminate\Foundation\Http\FormRequest;

class NurseRequest extends FormRequest
{
    public function getData(): NurseData
    {
        return new NurseData(
            nurseID: $this->filled('nurseID') ? $this->integer('nurseID') : null,
            name: $this->string('name')->toString(),
            position: $this->enum('position', NursePosition::class),
            ssn: $this->filled('ssn') ? $this->string('ssn')->toString() : null,
        );
    }
}
