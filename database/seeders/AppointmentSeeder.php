<?php

namespace Database\Seeders;

use App\Clinical\Entities\Models\Appointment;
use App\Hospital\Entities\HospitalCalendar;
use App\Patient\Entities\Models\Patient;
use App\Staff\Entities\Models\Nurse;
use App\Staff\Entities\Models\Physician;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class AppointmentSeeder extends Seeder
{
    private const float APPOINTMENTS_PER_PATIENT_PER_WEEKDAY = 0.0015;

    private const int DAYS_SCHEDULED_AHEAD = 21;

    private const int FIRST_SLOT_MINUTE = 8 * 60;

    private const int LAST_SLOT_MINUTE = 16 * 60 + 30;

    private const int SLOT_MINUTES = 15;

    private const int PERCENT_WITH_PRIMARY_PHYSICIAN = 70;

    private const array DURATION_WEIGHTS = [15 => 15, 30 => 60, 45 => 15, 60 => 10];

    private const int ROWS_PER_INSERT = 1000;

    private array $patients = [];

    private array $physicianIds = [];

    private array $nurseIds = [];

    private array $nursesOnCallByDay = [];

    private array $rows = [];

    public function run(): void
    {
        if (Appointment::query()->withoutGlobalScope(SoftDeletingScope::class)->exists()) {
            return;
        }

        $this->physicianIds = Physician::query()->whereCapableOfPrescribingBasedOnPosition()->pluck('id')->all();
        $this->nurseIds = Nurse::query()->pluck('id')->all();
        $this->patients = Patient::query()->orderBy('created_at')->get(['id', 'created_at', 'primary_physician_id'])
            ->map(fn (Patient $patient): array => [
                'id' => $patient->id,
                'registeredOn' => $patient->created_at->setTimezone(HospitalCalendar::timezone())->toDateString(),
                'primaryPhysicianId' => in_array($patient->primary_physician_id, $this->physicianIds, true) ? $patient->primary_physician_id : null,
            ])
            ->all();

        if ($this->patients === [] || $this->physicianIds === [] || $this->nurseIds === []) {
            return;
        }

        $this->nursesOnCallByDay = $this->nursesOnCallByDay();
        $lastDay = HospitalCalendar::today()->addDays(self::DAYS_SCHEDULED_AHEAD);
        $registeredPatients = 0;

        for ($day = CarbonImmutable::parse($this->patients[0]['registeredOn']); $day->lessThanOrEqualTo($lastDay); $day = $day->addDay()) {
            while ($registeredPatients < count($this->patients) && $this->patients[$registeredPatients]['registeredOn'] <= $day->toDateString()) {
                $registeredPatients++;
            }

            if ($day->isWeekend()) {
                continue;
            }

            foreach (range(1, $this->numberOfAppointmentsOn($registeredPatients)) as $ignored) {
                $this->bookAppointment($day, $this->patients[random_int(0, $registeredPatients - 1)]);
            }
        }

        collect($this->rows)
            ->chunk(self::ROWS_PER_INSERT)
            ->each(fn ($rows) => DB::table('Appointment')->insert($rows->values()->all()));
    }

    private function numberOfAppointmentsOn(int $registeredPatients): int
    {
        $expected = $registeredPatients * self::APPOINTMENTS_PER_PATIENT_PER_WEEKDAY;
        $wholeAppointments = (int) floor($expected);

        return max(1, $wholeAppointments + (random_int(0, 999) < ($expected - $wholeAppointments) * 1000 ? 1 : 0));
    }

    private function bookAppointment(CarbonImmutable $day, array $patient): void
    {
        $slots = intdiv(self::LAST_SLOT_MINUTE - self::FIRST_SLOT_MINUTE, self::SLOT_MINUTES);
        $startsAt = CarbonImmutable::create($day->year, $day->month, $day->day, 0, 0, 0, HospitalCalendar::timezone())
            ->addMinutes(self::FIRST_SLOT_MINUTE + random_int(0, $slots) * self::SLOT_MINUTES)
            ->utc();
        $endsAt = $startsAt->addMinutes($this->randomDuration());
        $bookedAt = $startsAt->subDays(random_int(1, 30))->min(CarbonImmutable::now());

        $this->rows[] = [
            'id' => Str::lower((string) Str::ulid($startsAt)),
            'patient_id' => $patient['id'],
            'physician_id' => $patient['primaryPhysicianId'] !== null && random_int(1, 100) <= self::PERCENT_WITH_PRIMARY_PHYSICIAN
                ? $patient['primaryPhysicianId']
                : $this->physicianIds[array_rand($this->physicianIds)],
            'nurse_id' => $this->pick($this->nursesOnCallByDay[$day->toDateString()] ?? $this->nurseIds),
            'startDateTime' => $startsAt->toDateTimeString(),
            'endDateTime' => $endsAt->toDateTimeString(),
            'created_at' => $bookedAt->toDateTimeString(),
            'updated_at' => $bookedAt->toDateTimeString(),
        ];
    }

    private function randomDuration(): int
    {
        $roll = random_int(1, array_sum(self::DURATION_WEIGHTS));

        foreach (self::DURATION_WEIGHTS as $minutes => $weight) {
            $roll -= $weight;

            if ($roll <= 0) {
                return $minutes;
            }
        }

        return 30;
    }

    private function nursesOnCallByDay(): array
    {
        $nursesOnCall = [];

        foreach (DB::table('OnCall')->join('Nurse', 'Nurse.id', '=', 'OnCall.nurse_id')->whereNull('Nurse.deleted_at')->get(['OnCall.nurse_id', 'OnCall.startDate', 'OnCall.endDate']) as $onCall) {
            for ($day = CarbonImmutable::parse($onCall->startDate); $day->lessThanOrEqualTo(CarbonImmutable::parse($onCall->endDate)); $day = $day->addDay()) {
                $nursesOnCall[$day->toDateString()][] = $onCall->nurse_id;
            }
        }

        return $nursesOnCall;
    }

    private function pick(array $ids): string
    {
        return $ids[array_rand($ids)];
    }
}
