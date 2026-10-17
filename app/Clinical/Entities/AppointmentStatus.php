<?php

namespace App\Clinical\Entities;

use App\Foundation\Entities\Concerns\HasValues;

enum AppointmentStatus: string
{
    use HasValues;

    case Upcoming = 'upcoming';
    case InProgress = 'in_progress';
    case Completed = 'completed';
}
