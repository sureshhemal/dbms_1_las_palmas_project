<?php

namespace App\Hospital\IO\Http\Requests;

use App\Hospital\Entities\RoomType;
use App\Hospital\IO\Http\DTOs\RoomData;
use Illuminate\Foundation\Http\FormRequest;

class RoomRequest extends FormRequest
{
    public function getData(): RoomData
    {
        return new RoomData(
            roomID: $this->filled('roomID') ? $this->integer('roomID') : null,
            roomType: $this->enum('roomType', RoomType::class),
        );
    }
}
