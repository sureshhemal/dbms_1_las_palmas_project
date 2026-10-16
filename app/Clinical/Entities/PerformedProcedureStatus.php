<?php

namespace App\Clinical\Entities;

use App\Foundation\Entities\Concerns\HasValues;

enum PerformedProcedureStatus: string
{
    use HasValues;

    case Performed = 'performed';
    case Scheduled = 'scheduled';
}
