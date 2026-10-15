<?php

namespace Database\Seeders;

use App\Hospital\Entities\DepartmentName;
use App\Hospital\Entities\Models\Department;
use App\Staff\Entities\Models\Physician;
use App\Staff\Entities\PhysicianPosition;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use stdClass;

class AffiliatedWithSeeder extends Seeder
{
    public function run(): void
    {
        $departments = Department::query()->get();

        $affiliations = Physician::query()
            ->doesntHave('departments')
            ->toBase()
            ->get(['id', 'position'])
            ->flatMap(fn (stdClass $physician): array => array_map(
                fn (string $departmentId): array => [
                    'physician_id' => $physician->id,
                    'department_id' => $departmentId,
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                $this->departmentIdsFor(PhysicianPosition::from($physician->position), $departments),
            ));

        if ($affiliations->isNotEmpty()) {
            DB::table('AffiliatedWith')->insert($affiliations->all());
        }
    }

    private function departmentIdsFor(PhysicianPosition $position, Collection $departments): array
    {
        $departmentIdsNamed = fn (DepartmentName ...$names): array => $departments
            ->whereIn('name', $names)
            ->pluck('id')
            ->all();

        return match ($position) {
            PhysicianPosition::ChiefOfMedicine, PhysicianPosition::Senior => $departmentIdsNamed(DepartmentName::GeneralMedicine),
            PhysicianPosition::Surgeon => $departmentIdsNamed(DepartmentName::Surgery),
            PhysicianPosition::Psychiatrist => $departmentIdsNamed(DepartmentName::Psychiatry),
            PhysicianPosition::Resident, PhysicianPosition::Intern => $departments->random(random_int(1, 2))->pluck('id')->all(),
        };
    }
}
