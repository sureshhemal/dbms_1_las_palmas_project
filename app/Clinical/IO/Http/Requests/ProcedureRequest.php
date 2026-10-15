<?php

namespace App\Clinical\IO\Http\Requests;

use App\Clinical\IO\Http\DTOs\ProcedureData;
use Illuminate\Foundation\Http\FormRequest;

class ProcedureRequest extends FormRequest
{
    public function getData(): ProcedureData
    {
        return new ProcedureData(
            procID: $this->filled('procID') ? $this->integer('procID') : null,
            name: $this->filled('name') ? $this->string('name')->trim()->toString() : null,
            cost: $this->filled('cost') ? $this->float('cost') : null,
        );
    }
}
