<?php

namespace App\Hospital\Entities;

use App\Foundation\Entities\Concerns\HasValues;

enum DepartmentName: string
{
    use HasValues;

    case GeneralMedicine = 'General Medicine';
    case Surgery = 'Surgery';
    case Psychiatry = 'Psychiatry';
}
