<?php

namespace Database\Factories\Clinical;

use App\Clinical\Entities\Models\Procedure;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProcedureFactory extends Factory
{
    protected $model = Procedure::class;

    public function definition(): array
    {
        return [
            'name' => ucfirst(fake()->unique()->word()).' '.fake()->randomElement(['Biopsy', 'Repair', 'Scan', 'Resection', 'Therapy']),
            'cost' => fake()->randomFloat(2, 50, 50000),
        ];
    }
}
