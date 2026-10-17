<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('Appointment')->whereNull('endDateTime')->update(['endDateTime' => DB::raw('startDateTime + interval 30 minute')]);

        Schema::table('Appointment', function (Blueprint $table) {
            $table->dateTime('endDateTime')->nullable(false)->change();
        });
    }

    public function down(): void
    {
        Schema::table('Appointment', function (Blueprint $table) {
            $table->dateTime('endDateTime')->nullable()->change();
        });
    }
};
