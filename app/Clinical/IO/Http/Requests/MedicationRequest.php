<?php

namespace App\Clinical\IO\Http\Requests;

use App\Clinical\IO\Http\DTOs\MedicationData;
use Illuminate\Foundation\Http\FormRequest;

class MedicationRequest extends FormRequest
{
    public function getData(): MedicationData
    {
        return new MedicationData(
            medID: $this->filled('medID') ? $this->integer('medID') : null,
            name: $this->filled('name') ? $this->string('name')->trim()->toString() : null,
        );
    }
}
