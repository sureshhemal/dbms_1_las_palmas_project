<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('Medication', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->integer('medID')->unique();
            $table->string('name', 40);
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::table('Medication', function (Blueprint $table) {
            $table->integer('medID')->autoIncrement()->change();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('Medication');
    }
};
