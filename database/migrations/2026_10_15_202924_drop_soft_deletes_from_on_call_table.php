<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('OnCall')->whereNotNull('deleted_at')->delete();

        Schema::table('OnCall', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });
    }

    public function down(): void
    {
        Schema::table('OnCall', function (Blueprint $table) {
            $table->softDeletes();
        });
    }
};
