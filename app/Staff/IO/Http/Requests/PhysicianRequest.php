<?php

namespace App\Staff\IO\Http\Requests;

use App\Staff\Entities\PhysicianPosition;
use App\Staff\IO\Http\DTOs\PhysicianData;
use Illuminate\Foundation\Http\FormRequest;

class PhysicianRequest extends FormRequest
{
    public function getData(): PhysicianData
    {
        return new PhysicianData(
            physicianID: $this->filled('physicianID') ? $this->integer('physicianID') : null,
            name: $this->string('name')->toString(),
            position: $this->enum('position', PhysicianPosition::class),
            ssn: $this->filled('ssn') ? $this->string('ssn')->toString() : null,
        );
    }
}
