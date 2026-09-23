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
        Schema::create('AffiliatedWith', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignUlid('physician_id')->constrained('Physician', 'id')->cascadeOnDelete();
            $table->foreignUlid('department_id')->constrained('Department', 'id')->cascadeOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['physician_id', 'department_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('AffiliatedWith');
    }
};
