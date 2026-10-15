<?php

namespace Database\Factories\Hospital;

use App\Hospital\Entities\Models\Room;
use App\Hospital\Entities\RoomType;
use Illuminate\Database\Eloquent\Factories\Factory;

class RoomFactory extends Factory
{
    protected $model = Room::class;

    public function definition(): array
    {
        return [
            'roomType' => fake()->randomElement(RoomType::cases()),
        ];
    }

    public function withType(RoomType $roomType): static
    {
        return $this->state(fn (array $attributes) => [
            'roomType' => $roomType,
        ]);
    }
}
