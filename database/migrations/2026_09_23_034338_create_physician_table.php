<?php

use App\Staff\Entities\PhysicianPosition;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('Physician', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->integer('physicianID')->unique();
            $table->string('name', 40);
            $table->string('position', 40);
            $table->string('ssn', 11)->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::table('Physician', function (Blueprint $table) {
            $table->integer('physicianID')->autoIncrement()->change();
        });

        $positions = collect(PhysicianPosition::values())->map(fn (string $value): string => DB::escape($value))->implode(', ');

        DB::statement("alter table `Physician` add constraint `physician_position_check` check (position in ({$positions}))");
    }

    public function down(): void
    {
        Schema::dropIfExists('Physician');
    }
};
