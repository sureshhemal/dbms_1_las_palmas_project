<?php

namespace Database\Seeders;

use App\Staff\Entities\Models\Nurse;
use App\Staff\Entities\NursePosition;
use Illuminate\Database\Seeder;

class NurseSeeder extends Seeder
{
    public function run(): void
    {
        foreach (NursePosition::cases() as $position) {
            Nurse::factory()
                ->count($this->numberOfNursesIn($position))
                ->withPosition($position)
                ->state(fn (): array => ['nurseID' => fake()->unique()->numberBetween(10000, 99999)])
                ->create();
        }
    }

    private function numberOfNursesIn(NursePosition $position): int
    {
        return match ($position) {
            NursePosition::HeadNurse => 8,
            NursePosition::Nurse => 120,
        };
    }
}
