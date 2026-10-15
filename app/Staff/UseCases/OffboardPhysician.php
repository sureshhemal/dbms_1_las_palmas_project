<?php

namespace App\Staff\UseCases;

use App\Hospital\Entities\Models\Department;
use App\Staff\Entities\Models\Physician;
use Illuminate\Support\Facades\DB;

final class OffboardPhysician
{
    public function execute(Physician $physician): void
    {
        DB::transaction(function () use ($physician): void {
            $this->removeFromDepartmentHead($physician);
            $this->removeFromDepartments($physician);

            $physician->delete();
        });
    }

    private function removeFromDepartmentHead(Physician $physician): void
    {
        Department::query()->where('head_id', $physician->id)->update(['head_id' => null]);
    }

    private function removeFromDepartments(Physician $physician): void
    {
        $physician->departments()->detach();
    }
}
