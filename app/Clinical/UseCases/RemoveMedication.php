<?php

namespace App\Clinical\UseCases;

use App\Clinical\Entities\Models\Medication;

final class RemoveMedication
{
    public function execute(Medication $medication): void
    {
        $medication->delete();
    }
}
