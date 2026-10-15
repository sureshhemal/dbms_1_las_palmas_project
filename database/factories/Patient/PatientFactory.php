<?php

namespace Database\Factories\Patient;

use App\Patient\Entities\Models\Patient;
use App\Staff\Entities\Models\Physician;
use Illuminate\Database\Eloquent\Factories\Factory;

class PatientFactory extends Factory
{
    protected $model = Patient::class;

    public function definition(): array
    {
        return [
            'name' => fake()->firstName().' '.fake()->lastName(),
            'ssn' => fake()->numerify('#########'),
            'address' => fake()->streetAddress().', '.fake()->city(),
            'dob' => fake()->dateTimeBetween('-90 years', '-1 day')->format('Y-m-d'),
            'phone' => fake()->numerify('(###) ###-####'),
            'insuranceNumber' => fake()->numberBetween(10000000, 99999999),
        ];
    }

    public function withPrimaryPhysician(Physician $physician): static
    {
        return $this->state(fn (array $attributes) => [
            'primary_physician_id' => $physician->id,
        ]);
    }
}
