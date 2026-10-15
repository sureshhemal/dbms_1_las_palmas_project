<?php

namespace App\Hospital\IO\Http\Requests;

use App\Hospital\IO\Http\DTOs\DepartmentData;
use Illuminate\Foundation\Http\FormRequest;

class DepartmentRequest extends FormRequest
{
    public function getData(): DepartmentData
    {
        return new DepartmentData(
            deptID: $this->filled('deptID') ? $this->integer('deptID') : null,
            headId: $this->filled('head_id') ? $this->string('head_id')->toString() : null,
        );
    }
}
