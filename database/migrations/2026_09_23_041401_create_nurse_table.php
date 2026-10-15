<?php

use App\Staff\Entities\NursePosition;
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
        Schema::create('Nurse', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->integer('nurseID')->unique();
            $table->string('name', 40);
            $table->string('position', 40);
            $table->string('ssn', 11)->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        $positions = collect(NursePosition::values())->map(fn (string $value): string => DB::escape($value))->implode(', ');

        DB::statement("alter table `Nurse` add constraint `nurse_position_check` check (position in ({$positions}))");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('Nurse');
    }
};
