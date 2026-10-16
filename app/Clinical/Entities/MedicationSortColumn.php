<?php

namespace App\Clinical\Entities;

enum MedicationSortColumn: string
{
    case Name = 'name';
    case PrescriptionShare = 'prescriptionShare';
}
