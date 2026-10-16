<?php

namespace App\Clinical\Entities;

enum ProcedureSortColumn: string
{
    case Name = 'name';
    case PerformedShare = 'performedShare';
}
