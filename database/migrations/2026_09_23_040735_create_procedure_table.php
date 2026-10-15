<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('Procedure', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->integer('procID')->unique();
            $table->string('name', 40);
            $table->double('cost')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::table('Procedure', function (Blueprint $table) {
            $table->integer('procID')->autoIncrement()->change();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('Procedure');
    }
};
