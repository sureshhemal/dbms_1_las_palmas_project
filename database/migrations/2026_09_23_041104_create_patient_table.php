<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('Patient', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->integer('patientID')->unique();
            $table->string('ssn', 11)->nullable();
            $table->string('name', 40);
            $table->string('address', 100)->nullable();
            $table->date('dob')->nullable();
            $table->string('phone', 16)->nullable();
            $table->integer('insuranceNumber')->nullable();
            $table->foreignUlid('primary_physician_id')->nullable()->constrained('Physician', 'id')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::table('Patient', function (Blueprint $table) {
            $table->integer('patientID')->autoIncrement()->change();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('Patient');
    }
};
