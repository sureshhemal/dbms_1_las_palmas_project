<?php

namespace Database\Seeders;

use App\Clinical\Entities\Models\Procedure;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ProcedureSeeder extends Seeder
{
    public function run(): void
    {
        if (Procedure::query()->withTrashed()->exists()) {
            return;
        }

        $file = fopen(database_path('seeders/data/procedures.csv'), 'r');

        if ($file === false) {
            return;
        }

        fgetcsv($file);
        $procedures = [];

        while (($row = fgetcsv($file)) !== false) {
            [$name, $cost] = $row;

            $procedures[] = [
                'id' => (string) Str::ulid(),
                'name' => $name,
                'cost' => $cost === '' || $cost === null ? null : (float) $cost,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        fclose($file);

        DB::table('Procedure')->insert($procedures);
    }
}
