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
        Schema::create('Undergoes', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignUlid('patient_id')->constrained('Patient', 'id');
            $table->foreignUlid('procedure_id')->constrained('Procedure', 'id');
            $table->foreignUlid('stay_id')->constrained('Stay', 'id');
            $table->foreignUlid('physician_id')->constrained('Physician', 'id');
            $table->foreignUlid('nurse_id')->constrained('Nurse', 'id');
            $table->date('procDate');
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['patient_id', 'procedure_id', 'stay_id', 'procDate'], 'undergoes_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('Undergoes');
    }
};
