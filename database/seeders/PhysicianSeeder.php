<?php

namespace Database\Seeders;

use App\Staff\Entities\Models\Physician;
use App\Staff\Entities\PhysicianPosition;
use Illuminate\Database\Seeder;

class PhysicianSeeder extends Seeder
{
    public function run(): void
    {
        foreach (PhysicianPosition::cases() as $position) {
            Physician::factory()
                ->count($this->numberOfPhysiciansIn($position))
                ->withPosition($position)
                ->state(fn (): array => ['physicianID' => fake()->unique()->numberBetween(10000, 99999)])
                ->create();
        }
    }

    private function numberOfPhysiciansIn(PhysicianPosition $position): int
    {
        return match ($position) {
            PhysicianPosition::ChiefOfMedicine => 1,
            PhysicianPosition::Senior => 20,
            PhysicianPosition::Surgeon => 12,
            PhysicianPosition::Psychiatrist => 6,
            PhysicianPosition::Resident => 24,
            PhysicianPosition::Intern => 12,
        };
    }
}
