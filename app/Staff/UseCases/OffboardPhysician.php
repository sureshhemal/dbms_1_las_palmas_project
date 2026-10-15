<?php

namespace App\Staff\UseCases;

use App\Staff\Entities\Models\Physician;

final class OffboardPhysician
{
    public function execute(Physician $physician): void
    {
        $physician->delete();
    }
}
