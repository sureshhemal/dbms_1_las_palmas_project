<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('AffiliatedWith', function (Blueprint $table) {
            $table->foreignUlid('physician_id')->constrained('Physician', 'id')->cascadeOnDelete();
            $table->foreignUlid('department_id')->constrained('Department', 'id')->cascadeOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->primary(['physician_id', 'department_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('AffiliatedWith');
    }
};
