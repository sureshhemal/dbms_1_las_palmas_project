<?php

namespace Database\Factories\Staff;

use App\Staff\Entities\Models\Nurse;
use App\Staff\Entities\NursePosition;
use Illuminate\Database\Eloquent\Factories\Factory;

class NurseFactory extends Factory
{
    protected $model = Nurse::class;

    public function definition(): array
    {
        return [
            'name' => fake()->firstName().' '.fake()->lastName(),
            'position' => fake()->randomElement(NursePosition::cases()),
            'ssn' => fake()->numerify('#########'),
        ];
    }

    public function withPosition(NursePosition $position): static
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
