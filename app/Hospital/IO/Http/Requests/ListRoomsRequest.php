<?php

namespace App\Hospital\IO\Http\Requests;

use App\Hospital\Entities\RoomType;
use App\Hospital\IO\Http\DTOs\RoomsFilters;
use Illuminate\Foundation\Http\FormRequest;

class ListRoomsRequest extends FormRequest
{
    public function getFilters(): RoomsFilters
    {
        return new RoomsFilters(
            search: $this->string('search')->trim()->toString() ?: null,
            roomTypes: collect($this->array('room_types'))
                ->map(fn (mixed $roomType): ?RoomType => RoomType::tryFrom((string) $roomType))
                ->filter()
                ->values()
                ->all(),
            bedsFree: collect($this->array('beds_free'))
                ->filter(fn (mixed $bedsFree): bool => is_numeric($bedsFree))
                ->map(fn (mixed $bedsFree): int => (int) $bedsFree)
                ->values()
                ->all(),
        );
    }
}
