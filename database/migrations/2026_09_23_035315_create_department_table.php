<?php

use App\Hospital\Entities\DepartmentName;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('Department', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->string('name', 40);
            $table->foreignUlid('head_id')->nullable()->constrained('Physician', 'id')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();
        });

        $names = collect(DepartmentName::values())->map(fn (string $value): string => DB::escape($value))->implode(', ');

        DB::statement("alter table `Department` add constraint `department_name_check` check (name in ({$names}))");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('Department');
    }
};
