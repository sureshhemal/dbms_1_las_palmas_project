<?php

namespace App\Staff\Entities;

use App\Foundation\Entities\Concerns\HasValues;

enum PhysicianPosition: string
{
    use HasValues;

    case Intern = 'Intern';
    case Surgeon = 'Surgeon';
    case Senior = 'Senior';
    case ChiefOfMedicine = 'Chief of Medicine';
    case Resident = 'Resident';
    case Psychiatrist = 'Psychiatrist';
}
