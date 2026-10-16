<?php

namespace Database\Factories\Clinical;

use App\Clinical\Entities\Models\PerformedProcedure;
use App\Clinical\Entities\Models\Procedure;
use App\Clinical\Entities\Models\Stay;
use App\Staff\Entities\Models\Nurse;
use App\Staff\Entities\Models\Physician;
use App\Staff\Entities\PhysicianPosition;
use Illuminate\Database\Eloquent\Factories\Factory;

class PerformedProcedureFactory extends Factory
{
    protected $model = PerformedProcedure::class;

    public function definition(): array
    {
        return [
            'stay_id' => Stay::factory()->between('2026-10-05', '2026-10-10'),
            'patient_id' => fn (array $attributes): string => (string) Stay::query()->whereKey($attributes['stay_id'])->value('patient_id'),
            'procedure_id' => Procedure::factory(),
            'physician_id' => Physician::factory()->withPosition(PhysicianPosition::Surgeon),
            'nurse_id' => Nurse::factory(),
            'procDate' => '2026-10-07',
        ];
    }

    public function performedOn(string $procDate): static
    {
        return $this->state(fn (array $attributes) => [
            'stay_id' => Stay::factory()->between($procDate),
            'procDate' => $procDate,
        ]);
    }
}
