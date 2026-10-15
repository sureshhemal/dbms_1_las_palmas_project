<?php

namespace App\Staff\UseCases;

use App\Staff\Entities\Models\Nurse;

final class OffboardNurse
{
    public function execute(Nurse $nurse): void
    {
        $nurse->delete();
    }
}
