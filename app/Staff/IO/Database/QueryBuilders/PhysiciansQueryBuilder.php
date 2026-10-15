<?php

namespace App\Staff\IO\Database\QueryBuilders;

use App\Staff\Entities\PhysicianPosition;
use Illuminate\Database\Eloquent\Builder;

class PhysiciansQueryBuilder extends Builder
{
    public function whereCapableOfBeingDepartmentHeadBasedOnPosition(): self
    {
        return $this->whereIn('position', PhysicianPosition::capableOfBeingDepartmentHead());
    }
}
