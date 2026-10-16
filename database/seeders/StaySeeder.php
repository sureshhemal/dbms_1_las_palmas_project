<?php

namespace Database\Seeders;

use App\Clinical\Entities\Models\Stay;
use App\Hospital\Entities\HospitalCalendar;
use App\Hospital\Entities\Models\Room;
use App\Patient\Entities\Models\Patient;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class StaySeeder extends Seeder
{
    private const int FIRST_YEAR = 2022;

    private const float AVERAGE_EXTRA_DAYS_AFTER_FIRST = 3.5;

    private const int LONGEST_STAY_IN_DAYS = 30;

    private const int ROWS_PER_INSERT = 1000;

    private const int DAYS_SCHEDULED_AHEAD = 21;

    private const int PERCENT_OF_CURRENT_STAYS_WITH_A_PLANNED_END = 50;

    private const int MOST_DAYS_BOOKED_IN_ADVANCE = 14;

    private const int ATTEMPTS_TO_FIND_A_FREE_PATIENT = 50;

    private array $patientIds = [];

    private array $registrationDates = [];

    private array $patientBusyUntil = [];

    public function run(): void
    {
        if (Stay::query()->withoutGlobalScope(SoftDeletingScope::class)->exists()) {
            return;
        }

        $patients = Patient::query()->orderBy('created_at')->get(['id', 'created_at']);
        $this->patientIds = $patients->pluck('id')->all();
        $this->registrationDates = $patients
            ->map(fn (Patient $patient): string => $patient->created_at->toDateString())
            ->all();
        $beds = $this->beds();

        if ($this->patientIds === [] || $beds === []) {
            return;
        }

        $today = HospitalCalendar::today();
        $lastSchedulingDay = $today->addDays(self::DAYS_SCHEDULED_AHEAD);
        $bedFreeFrom = array_map(
            fn (): CarbonImmutable => CarbonImmutable::create(self::FIRST_YEAR)->addDays(random_int(0, 3)),
            $beds,
        );
        $stays = [];

        while ($bedFreeFrom !== []) {
            $bed = array_keys($bedFreeFrom, min($bedFreeFrom))[0];
            $startDate = $bedFreeFrom[$bed];

            if ($startDate->greaterThan($lastSchedulingDay)) {
                unset($bedFreeFrom[$bed]);

                continue;
            }

            $endDate = $startDate->addDays($this->lengthOfStay());
            $patientId = $this->freePatientOn($startDate);

            if ($patientId === null) {
                $bedFreeFrom[$bed] = $startDate->addDay();

                continue;
            }

            $isScheduled = $startDate->greaterThan($today);
            $isOpenEnded = ! $isScheduled
                && $endDate->greaterThan($today)
                && random_int(1, 100) > self::PERCENT_OF_CURRENT_STAYS_WITH_A_PLANNED_END;
            $recordedAt = $isScheduled
                ? $this->bookingMoment($startDate)
                : $startDate->setTime(random_int(7, 20), random_int(0, 59));
            $stays[] = [
                'id' => Str::lower((string) Str::ulid($recordedAt)),
                'patient_id' => $patientId,
                'room_id' => $beds[$bed],
                'startDate' => $startDate->toDateString(),
                'endDate' => $isOpenEnded ? null : $endDate->toDateString(),
                'created_at' => $recordedAt,
                'updated_at' => $recordedAt,
            ];
            $this->patientBusyUntil[$patientId] = $isOpenEnded ? CarbonImmutable::create(9999) : $endDate;

            if ($isOpenEnded) {
                unset($bedFreeFrom[$bed]);
            } else {
                $bedFreeFrom[$bed] = $endDate->addDays($this->daysBedStaysEmpty($endDate));
            }
        }

        collect($stays)
            ->sortBy('startDate')
            ->chunk(self::ROWS_PER_INSERT)
            ->each(fn ($rows) => DB::table('Stay')->insert($rows->values()->all()));
    }

    private function bookingMoment(CarbonImmutable $startDate): CarbonImmutable
    {
        $bookedAt = $startDate->subDays(random_int(1, self::MOST_DAYS_BOOKED_IN_ADVANCE))->setTime(random_int(8, 17), random_int(0, 59));
        $now = CarbonImmutable::now();

        return $bookedAt->greaterThan($now) ? $now->subMinutes(random_int(1, 600)) : $bookedAt;
    }

    private function beds(): array
    {
        return Room::query()->orderBy('roomID')->get(['id', 'roomType'])
            ->flatMap(fn (Room $room): array => array_fill(0, $room->roomType->beds(), $room->id))
            ->all();
    }

    private function lengthOfStay(): int
    {
        return min(self::LONGEST_STAY_IN_DAYS, 1 + $this->exponentialDays(self::AVERAGE_EXTRA_DAYS_AFTER_FIRST));
    }

    private function daysBedStaysEmpty(CarbonImmutable $day): int
    {
        $occupancy = match ($day->month) {
            12, 1, 2 => 0.85,
            6, 7, 8 => 0.68,
            default => 0.75,
        };
        $averageStay = 1 + self::AVERAGE_EXTRA_DAYS_AFTER_FIRST;

        return $this->exponentialDays($averageStay * (1 - $occupancy) / $occupancy);
    }

    private function exponentialDays(float $averageDays): int
    {
        $uniform = (random_int(1, PHP_INT_MAX - 1) / PHP_INT_MAX);
        $roundingDownLosesHalfADay = 0.5;

        return (int) floor(-log($uniform) * ($averageDays + $roundingDownLosesHalfADay));
    }

    private function freePatientOn(CarbonImmutable $day): ?string
    {
        $registeredPatients = $this->registeredPatientsBy($day->toDateString());

        if ($registeredPatients === 0) {
            return null;
        }

        foreach (range(1, self::ATTEMPTS_TO_FIND_A_FREE_PATIENT) as $ignored) {
            $patientId = $this->patientIds[random_int(0, $registeredPatients - 1)];
            $busyUntil = $this->patientBusyUntil[$patientId] ?? null;

            if ($busyUntil === null || $busyUntil->lessThanOrEqualTo($day)) {
                return $patientId;
            }
        }

        return null;
    }

    private function registeredPatientsBy(string $day): int
    {
        $low = 0;
        $high = count($this->registrationDates);

        while ($low < $high) {
            $middle = intdiv($low + $high, 2);

            if ($this->registrationDates[$middle] <= $day) {
                $low = $middle + 1;
            } else {
                $high = $middle;
            }
        }

        return $low;
    }
}
