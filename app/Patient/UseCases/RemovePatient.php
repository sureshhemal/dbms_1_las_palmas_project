<?php

namespace App\Patient\UseCases;

use App\Patient\Entities\Models\Patient;

final class RemovePatient
{
    public function execute(Patient $patient): void
    {
        $patient->delete();
    }
}
