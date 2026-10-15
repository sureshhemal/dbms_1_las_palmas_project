<?php

namespace App\Hospital\IO\Http\Controllers;

use App\Hospital\Entities\Models\Department;
use Inertia\Inertia;
use Inertia\Response;

class DepartmentPageController
{
    public function show(Department $department): Response
    {
        return Inertia::render('Department', [
            'departmentId' => $department->id,
        ]);
    }
}
