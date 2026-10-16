<?php

namespace App\Clinical\Entities;

use App\Foundation\Entities\Concerns\HasValues;

enum StayStatus: string
{
    use HasValues;

    case Admitted = 'admitted';
    case Scheduled = 'scheduled';
    case Discharged = 'discharged';
}
