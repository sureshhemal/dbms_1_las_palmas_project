<?php

namespace App\Hospital\Entities;

use App\Foundation\Entities\Concerns\HasValues;

enum RoomType: string
{
    use HasValues;

    case Single = 'Single';
    case Double = 'Double';
}
