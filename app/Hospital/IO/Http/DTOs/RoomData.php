<?php

namespace App\Hospital\IO\Http\DTOs;

use App\Hospital\Entities\RoomType;

final readonly class RoomData
{
    public function __construct(
        public ?int $roomID,
        public ?RoomType $roomType,
    ) {}
}
