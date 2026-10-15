<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('Appointment', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->integer('appID')->unique();
            $table->foreignUlid('patient_id')->constrained('Patient', 'id')->cascadeOnDelete();
            $table->foreignUlid('nurse_id')->constrained('Nurse', 'id');
            $table->foreignUlid('physician_id')->constrained('Physician', 'id');
            $table->dateTime('startDateTime');
            $table->dateTime('endDateTime')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::table('Appointment', function (Blueprint $table) {
            $table->integer('appID')->autoIncrement()->change();
        });

        DB::statement('alter table `Appointment` add constraint `appointment_date_times_check` check (endDateTime > startDateTime)');
    }

    public function down(): void
    {
        Schema::dropIfExists('Appointment');
    }
};
