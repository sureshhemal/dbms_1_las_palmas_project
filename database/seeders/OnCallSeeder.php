<?php

namespace Database\Seeders;

use App\Hospital\Entities\HospitalCalendar;
use App\Staff\Entities\Models\Nurse;
use App\Staff\Entities\Models\OnCall;
use Carbon\CarbonImmutable;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class OnCallSeeder extends Seeder
{
    private const int NURSES_ON_CALL_AT_ONCE = 3;

    private const int DAYS_BEFORE_TODAY = 60;

    private const int DAYS_AFTER_TODAY = 60;

    private array $onCallPeriodsByNurse = [];

    public function run(): void
    {
        if (OnCall::query()->exists()) {
            return;
        }

        $nurseIds = Nurse::query()->pluck('id')->all();

        if (count($nurseIds) <= self::NURSES_ON_CALL_AT_ONCE) {
            return;
        }

        $today = HospitalCalendar::today();
        $rotaStart = $today->subDays(self::DAYS_BEFORE_TODAY);
        $rotaEnd = $today->addDays(self::DAYS_AFTER_TODAY);

        foreach (range(1, self::NURSES_ON_CALL_AT_ONCE) as $ignored) {
            $this->planRotaLane($nurseIds, $rotaStart->addDays(random_int(0, 3)), $rotaEnd);
        }

        DB::table('OnCall')->insert($this->onCallRows());
    }

    private function planRotaLane(array $nurseIds, CarbonImmutable $startDate, CarbonImmutable $rotaEnd): void
    {
        while ($startDate->lessThan($rotaEnd)) {
            $endDate = $startDate->addDays(random_int(2, 6));
            $nurseId = $this->randomNurseFreeBetween($nurseIds, $startDate, $endDate);

            $this->onCallPeriodsByNurse[$nurseId][] = [$startDate, $endDate];

            $startDate = $endDate->addDay();
        }
    }

    private function randomNurseFreeBetween(array $nurseIds, CarbonImmutable $startDate, CarbonImmutable $endDate): string
    {
        do {
            $nurseId = $nurseIds[array_rand($nurseIds)];
        } while ($this->isOnCallBetween($nurseId, $startDate, $endDate));

        return $nurseId;
    }

    private function isOnCallBetween(string $nurseId, CarbonImmutable $startDate, CarbonImmutable $endDate): bool
    {
        foreach ($this->onCallPeriodsByNurse[$nurseId] ?? [] as [$periodStart, $periodEnd]) {
            if ($periodStart->lessThanOrEqualTo($endDate) && $periodEnd->greaterThanOrEqualTo($startDate)) {
                return true;
            }
        }

        return false;
    }

    private function onCallRows(): array
    {
        $rows = [];

        foreach ($this->onCallPeriodsByNurse as $nurseId => $periods) {
            foreach ($periods as [$startDate, $endDate]) {
                $rows[] = [
                    'id' => (string) Str::ulid(),
                    'nurse_id' => $nurseId,
                    'startDate' => $startDate->toDateString(),
                    'endDate' => $endDate->toDateString(),
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }
        }

        return $rows;
    }
}
