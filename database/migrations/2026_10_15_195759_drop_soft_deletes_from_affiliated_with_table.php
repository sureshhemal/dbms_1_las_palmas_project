<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('AffiliatedWith')->whereNotNull('deleted_at')->delete();

        Schema::table('AffiliatedWith', function (Blueprint $table) {
            $table->dropSoftDeletes();
        });
    }

    public function down(): void
    {
        Schema::table('AffiliatedWith', function (Blueprint $table) {
            $table->softDeletes();
        });
    }
};
