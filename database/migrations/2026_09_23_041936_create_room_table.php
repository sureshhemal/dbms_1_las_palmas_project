<?php

use App\Hospital\Entities\RoomType;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('Room', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->integer('roomID')->unique();
            $table->string('roomType', 40);
            $table->timestamps();
            $table->softDeletes();
        });

        $roomTypes = collect(RoomType::values())->map(fn (string $value): string => DB::escape($value))->implode(', ');

        DB::statement("alter table `Room` add constraint `room_room_type_check` check (roomType in ({$roomTypes}))");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('Room');
    }
};
