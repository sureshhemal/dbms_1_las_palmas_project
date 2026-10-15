<?php

namespace App\Staff\UseCases;

use App\Staff\Entities\Models\OnCall;

final class RemoveOnCall
{
    public function execute(OnCall $onCall): void
    {
        $onCall->delete();
    }
}
