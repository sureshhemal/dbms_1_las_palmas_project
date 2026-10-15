<?php

namespace Database\Factories\Staff;

use App\Staff\Entities\Models\Physician;
use App\Staff\Entities\PhysicianPosition;
use Illuminate\Database\Eloquent\Factories\Factory;

class PhysicianFactory extends Factory
{
    protected $model = Physician::class;

    public function definition(): array
    {
        return [
            'name' => fake()->firstName().' '.fake()->lastName(),
            'position' => fake()->randomElement(PhysicianPosition::cases()),
            'ssn' => fake()->numerify('#########'),
        ];
    }

    public function withPosition(PhysicianPosition $position): static
    {
        return $this->state(fn (array $attributes) => [
            'position' => $position,
        ]);
    }

    public function withoutSsn(): static
    {
        return $this->state(fn (array $attributes) => [
            'ssn' => null,
        ]);
    }
}
