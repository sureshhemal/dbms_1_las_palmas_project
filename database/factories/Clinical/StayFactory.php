<?php

namespace Database\Factories\Clinical;

use App\Clinical\Entities\Models\Stay;
use App\Hospital\Entities\Models\Room;
use App\Patient\Entities\Models\Patient;
use Illuminate\Database\Eloquent\Factories\Factory;

class StayFactory extends Factory
{
    protected $model = Stay::class;

    public function definition(): array
    {
        return [
            'patient_id' => Patient::factory()->state(['created_at' => '2020-01-01 09:00:00']),
            'room_id' => Room::factory(),
            'startDate' => '2026-01-01',
            'endDate' => '2026-01-04',
        ];
    }

    public function between(string $startDate, ?string $endDate = null): static
    {
        return $this->state(fn (array $attributes) => [
            'startDate' => $startDate,
            'endDate' => $endDate,
        ]);
    }
}
