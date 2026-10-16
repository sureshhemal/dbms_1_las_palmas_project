<?php

namespace Database\Factories\Clinical;

use App\Clinical\Entities\Models\Medication;
use App\Clinical\Entities\Models\Prescription;
use App\Patient\Entities\Models\Patient;
use App\Staff\Entities\Models\Physician;
use App\Staff\Entities\PhysicianPosition;
use Illuminate\Database\Eloquent\Factories\Factory;

class PrescriptionFactory extends Factory
{
    protected $model = Prescription::class;

    public function definition(): array
    {
        return [
            'physician_id' => Physician::factory()->withPosition(PhysicianPosition::Senior),
            'patient_id' => Patient::factory(),
            'medication_id' => Medication::factory(),
            'prescribedDate' => fake()->dateTimeBetween('-3 years', '-1 day')->format('Y-m-d'),
            'dose' => fake()->randomElement(['10 mg once daily', '500 mg twice daily', '1 tablet at bedtime']),
        ];
    }

    public function prescribedOn(string $prescribedDate): static
    {
        return $this->state(fn (array $attributes) => [
            'prescribedDate' => $prescribedDate,
        ]);
    }
}
