<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('OnCall', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignUlid('nurse_id')->constrained('Nurse', 'id')->cascadeOnDelete();
            $table->date('startDate');
            $table->date('endDate');
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['nurse_id', 'startDate', 'endDate'], 'oncall_unique');
        });

        DB::statement('alter table `OnCall` add constraint `oncall_dates_check` check (endDate > startDate)');
    }

    public function down(): void
    {
        Schema::dropIfExists('OnCall');
    }
};
