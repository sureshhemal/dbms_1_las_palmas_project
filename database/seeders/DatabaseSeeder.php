<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        $this->call(PhysicianSeeder::class);
        $this->call(NurseSeeder::class);
        $this->call(AffiliatedWithSeeder::class);
        $this->call(DepartmentHeadSeeder::class);
        $this->call(OnCallSeeder::class);
        $this->call(RoomSeeder::class);
        $this->call(MedicationSeeder::class);
        $this->call(ProcedureSeeder::class);
        $this->call(PatientSeeder::class);
    }
}
