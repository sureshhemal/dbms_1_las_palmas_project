<?php

namespace Database\Factories\Clinical;

use App\Clinical\Entities\Models\Appointment;
use App\Patient\Entities\Models\Patient;
use App\Staff\Entities\Models\Nurse;
use App\Staff\Entities\Models\Physician;
use App\Staff\Entities\PhysicianPosition;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Factories\Factory;

class AppointmentFactory extends Factory
{
    protected $model = Appointment::class;

    public function definition(): array
    {
        return [
            'patient_id' => Patient::factory(),
            'physician_id' => Physician::factory()->withPosition(PhysicianPosition::Senior),
            'nurse_id' => Nurse::factory(),
            'startDateTime' => '2026-10-20 15:00:00',
            'endDateTime' => '2026-10-20 15:30:00',
        ];
    }

    public function startingAt(string $startDateTime, int $minutes = 30): static
    {
        return $this->state(fn (array $attributes) => [
            'startDateTime' => $startDateTime,
            'endDateTime' => CarbonImmutable::parse($startDateTime)->addMinutes($minutes)->toDateTimeString(),
        ]);
    }
}
