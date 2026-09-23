<?php

namespace App\Staff\Entities;

use App\Foundation\Entities\Concerns\HasValues;

enum NursePosition: string
{
    use HasValues;

    case HeadNurse = 'Head Nurse';
    case Nurse = 'Nurse';
}
