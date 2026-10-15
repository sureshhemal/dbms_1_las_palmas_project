<?php

use App\Hospital\Entities\DepartmentName;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('Department')->insert(
            collect(DepartmentName::values())
                ->map(fn (string $name, int $position): array => [
                    'id' => (string) Str::ulid(),
                    'deptID' => 1001 + $position,
                    'name' => $name,
                    'created_at' => now(),
                    'updated_at' => now(),
                ])
                ->all(),
        );
    }

    public function down(): void
    {
        DB::table('Department')->whereIn('name', DepartmentName::values())->delete();
    }
};
