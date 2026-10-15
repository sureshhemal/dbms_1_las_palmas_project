<?php

namespace App\Staff\UseCases;

use App\Staff\Entities\Models\Nurse;
use App\Staff\Entities\Models\Physician;

final class CountStaff
{
    public function execute(): array
    {
        return [
            'physician_count' => Physician::count(),
            'nurse_count' => Nurse::count(),
        ];
    }
}
