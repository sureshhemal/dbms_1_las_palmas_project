<?php

namespace Database\Factories\Clinical;

use App\Clinical\Entities\Models\Medication;
use Illuminate\Database\Eloquent\Factories\Factory;

class MedicationFactory extends Factory
{
    protected $model = Medication::class;

    public function definition(): array
    {
        return [
            'name' => ucfirst(fake()->unique()->word()).' '.fake()->randomElement(['Hydrochloride', 'Sodium', 'Potassium', 'Acetate', 'Sulfate']),
        ];
    }
}
