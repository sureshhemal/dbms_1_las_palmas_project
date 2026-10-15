<?php

use App\Hospital\Entities\DepartmentName;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('Department', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->integer('deptID')->unique();
            $table->string('name', 40);
            $table->foreignUlid('head_id')->nullable()->constrained('Physician', 'id')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::table('Department', function (Blueprint $table) {
            $table->integer('deptID')->autoIncrement()->change();
        });

        $names = collect(DepartmentName::values())->map(fn (string $value): string => DB::escape($value))->implode(', ');

        DB::statement("alter table `Department` add constraint `department_name_check` check (name in ({$names}))");
    }

    public function down(): void
    {
        Schema::dropIfExists('Department');
    }
};
