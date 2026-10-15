<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('Stay', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->integer('stayID')->unique();
            $table->foreignUlid('patient_id')->constrained('Patient', 'id')->cascadeOnDelete();
            $table->foreignUlid('room_id')->constrained('Room', 'id');
            $table->date('startDate');
            $table->date('endDate')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::table('Stay', function (Blueprint $table) {
            $table->integer('stayID')->autoIncrement()->change();
        });

        DB::statement('alter table `Stay` add constraint `stay_dates_check` check (endDate > startDate)');
    }

    public function down(): void
    {
        Schema::dropIfExists('Stay');
    }
};
