<?php

namespace Database\Seeders;

use App\Clinical\Entities\Models\Medication;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class MedicationSeeder extends Seeder
{
    private const int ROWS_PER_INSERT = 500;

    public function run(): void
    {
        if (Medication::query()->withTrashed()->exists()) {
            return;
        }

        $medicationNames = file(database_path('seeders/data/medications.txt'), FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) ?: [];

        collect($medicationNames)
            ->map(fn (string $medicationName): array => [
                'id' => (string) Str::ulid(),
                'name' => $medicationName,
                'created_at' => now(),
                'updated_at' => now(),
            ])
            ->chunk(self::ROWS_PER_INSERT)
            ->each(fn ($rows) => DB::table('Medication')->insert($rows->values()->all()));
    }
}
