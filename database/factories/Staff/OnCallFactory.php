<?php

namespace Database\Factories\Staff;

use App\Staff\Entities\Models\Nurse;
use App\Staff\Entities\Models\OnCall;
use Illuminate\Database\Eloquent\Factories\Factory;

class OnCallFactory extends Factory
{
    protected $model = OnCall::class;

    public function definition(): array
    {
        $startDate = today()->addDays(fake()->numberBetween(-60, 60));

        return [
            'nurse_id' => Nurse::factory(),
            'startDate' => $startDate->toDateString(),
            'endDate' => $startDate->copy()->addDays(fake()->numberBetween(1, 7))->toDateString(),
        ];
    }

    public function between(string $startDate, string $endDate): static
    {
        return $this->state(fn (array $attributes) => [
            'startDate' => $startDate,
            'endDate' => $endDate,
        ]);
    }
}
