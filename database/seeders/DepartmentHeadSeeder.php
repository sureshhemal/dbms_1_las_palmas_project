<?php

namespace Database\Seeders;

use App\Hospital\Entities\DepartmentName;
use App\Hospital\Entities\Models\Department;
use App\Staff\Entities\Models\Physician;
use App\Staff\Entities\PhysicianPosition;
use Illuminate\Database\Seeder;

class DepartmentHeadSeeder extends Seeder
{
    public function run(): void
    {
        Department::query()
            ->whereNull('head_id')
            ->get()
            ->each(function (Department $department): void {
                $headId = Physician::query()
                    ->whereCapableOfBeingDepartmentHeadBasedOnPosition()
                    ->where('position', $this->fittingPositionFor($department->name))
                    ->inRandomOrder()
                    ->value('id');

                $department->update(['head_id' => $headId]);
            });
    }

    private function fittingPositionFor(DepartmentName $departmentName): PhysicianPosition
    {
        return match ($departmentName) {
            DepartmentName::GeneralMedicine => PhysicianPosition::ChiefOfMedicine,
            DepartmentName::Surgery => PhysicianPosition::Surgeon,
            DepartmentName::Psychiatry => PhysicianPosition::Psychiatrist,
        };
    }
}
