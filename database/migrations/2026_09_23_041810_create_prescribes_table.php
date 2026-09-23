<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('Prescribes', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignUlid('physician_id')->constrained('Physician', 'id');
            $table->foreignUlid('patient_id')->constrained('Patient', 'id')->cascadeOnDelete();
            $table->foreignUlid('medication_id')->constrained('Medication', 'id');
            $table->date('prescribedDate');
            $table->string('dose', 40)->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['physician_id', 'patient_id', 'medication_id', 'prescribedDate'], 'prescribes_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('Prescribes');
    }
};
