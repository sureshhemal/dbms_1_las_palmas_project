<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('Prescribes')->whereNull('dose')->update(['dose' => 'Not recorded']);

        Schema::table('Prescribes', function (Blueprint $table) {
            $table->string('dose', 40)->nullable(false)->change();
        });
    }

    public function down(): void
    {
        Schema::table('Prescribes', function (Blueprint $table) {
            $table->string('dose', 40)->nullable()->change();
        });
    }
};
