<?php

namespace Database\Seeders;

use App\Clinical\Entities\Models\Medication;
use App\Clinical\Entities\Models\Prescription;
use App\Hospital\Entities\HospitalCalendar;
use App\Patient\Entities\Models\Patient;
use App\Staff\Entities\Models\Physician;
use Carbon\CarbonImmutable;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class PrescriptionSeeder extends Seeder
{
    private const int FIRST_YEAR = 2022;

    private const int PRESCRIPTIONS_IN_FIRST_YEAR = 7500;

    private const float YEARLY_GROWTH = 0.15;

    private const int FORMULARY_SIZE = 400;

    private const int PERCENT_OF_REPEAT_PRESCRIPTIONS = 30;

    private const int PERCENT_BY_PRIMARY_PHYSICIAN = 75;

    private const int ROWS_PER_INSERT = 1000;

    private const array WINTER_MEDICATION_WORDS = ['oseltamivir', 'amoxicillin', 'azithromycin', 'cefdinir', 'cephalexin', 'doxycycline', 'benzonatate', 'guaifenesin', 'dextromethorphan', 'albuterol', 'clarithromycin', 'cefuroxime'];

    private const array SPRING_MEDICATION_WORDS = ['cetirizine', 'loratadine', 'fexofenadine', 'fluticasone', 'montelukast', 'azelastine', 'olopatadine'];

    private const array DOSES = [
        '5 mg once daily', '10 mg once daily', '20 mg once daily', '40 mg once daily', '25 mg twice daily',
        '50 mg twice daily', '100 mg once daily', '250 mg three times daily', '500 mg twice daily', '1 g once daily',
        '1 tablet at bedtime', '1 tablet as needed for pain', '2 puffs every 4 hours as needed', 'Apply thin layer twice daily',
        '5 ml three times daily', '1 injection once weekly', '1 drop in each eye twice daily',
    ];

    private array $formulary = [];

    private array $patients = [];

    private array $prescriberIds = [];

    private array $writtenPrescriptions = [];

    private array $recentPrescriptions = [];

    public function run(): void
    {
        if (Prescription::query()->withTrashed()->exists()) {
            return;
        }

        $this->prescriberIds = Physician::query()->whereCapableOfPrescribingBasedOnPosition()->pluck('id')->all();
        $this->patients = Patient::query()->orderBy('created_at')->get(['id', 'created_at', 'primary_physician_id'])
            ->map(fn (Patient $patient): array => [
                'id' => $patient->id,
                'registeredOn' => $patient->created_at?->toDateString(),
                'primaryPhysicianId' => in_array($patient->primary_physician_id, $this->prescriberIds, true) ? $patient->primary_physician_id : null,
            ])
            ->all();
        $this->formulary = $this->formulary();

        if ($this->patients === [] || $this->prescriberIds === [] || $this->formulary === []) {
            return;
        }

        $today = HospitalCalendar::today();
        $registeredPatients = 0;
        $monthlyWeights = [];

        for ($day = CarbonImmutable::create(self::FIRST_YEAR); $day->lessThanOrEqualTo($today); $day = $day->addDay()) {
            while ($registeredPatients < count($this->patients) && $this->patients[$registeredPatients]['registeredOn'] <= $day->toDateString()) {
                $registeredPatients++;
            }

            if ($registeredPatients === 0) {
                continue;
            }

            $monthKey = $day->format('Y-m');
            $monthlyWeights[$monthKey] ??= $this->cumulativeWeightsFor($day);

            foreach (range(1, $this->numberOfPrescriptionsOn($day)) as $ignored) {
                $this->writePrescription($day, $registeredPatients, $monthlyWeights[$monthKey]);
            }
        }

        collect($this->writtenPrescriptions)
            ->chunk(self::ROWS_PER_INSERT)
            ->each(fn ($rows) => DB::table('Prescribes')->insert($rows->values()->all()));
    }

    private function formulary(): array
    {
        return Medication::query()->orderBy('medID')->limit(self::FORMULARY_SIZE)->get(['id', 'name'])
            ->values()
            ->map(function (Medication $medication, int $rank): array {
                $name = strtolower($medication->name);
                $lifecycle = random_int(1, 100);

                return [
                    'id' => $medication->id,
                    'popularity' => 1 / (($rank + 1) ** 0.9),
                    'yearlyTrend' => random_int(-25, 30) / 100,
                    'firstYear' => $rank >= 50 && $lifecycle <= 5 ? random_int(2024, 2025) : self::FIRST_YEAR,
                    'lastYear' => $rank >= 50 && $lifecycle > 96 ? random_int(2023, 2024) : PHP_INT_MAX,
                    'isWinterMedication' => Str::contains($name, self::WINTER_MEDICATION_WORDS),
                    'isSpringMedication' => Str::contains($name, self::SPRING_MEDICATION_WORDS),
                ];
            })
            ->all();
    }

    private function cumulativeWeightsFor(CarbonImmutable $day): array
    {
        $yearsSinceStart = $day->year - self::FIRST_YEAR;
        $total = 0.0;
        $cumulativeWeights = [];

        foreach ($this->formulary as $medication) {
            $isPrescribedThisYear = $day->year >= $medication['firstYear'] && $day->year <= $medication['lastYear'];
            $yearlyTrend = $day->year >= 2024 && $medication['firstYear'] >= 2024 ? 0.5 : $medication['yearlyTrend'];
            $weight = $isPrescribedThisYear
                ? $medication['popularity'] * exp($yearlyTrend * $yearsSinceStart) * $this->seasonalBoost($medication, $day->month)
                : 0.0;

            $total += $weight;
            $cumulativeWeights[] = $total;
        }

        return $cumulativeWeights;
    }

    private function seasonalBoost(array $medication, int $month): float
    {
        return match (true) {
            $medication['isWinterMedication'] && in_array($month, [12, 1, 2], true) => 2.0,
            $medication['isWinterMedication'] && in_array($month, [11, 3], true) => 1.4,
            $medication['isSpringMedication'] && in_array($month, [4, 5], true) => 1.8,
            default => 1.0,
        };
    }

    private function numberOfPrescriptionsOn(CarbonImmutable $day): int
    {
        $prescriptionsThisYear = self::PRESCRIPTIONS_IN_FIRST_YEAR * ((1 + self::YEARLY_GROWTH) ** ($day->year - self::FIRST_YEAR));
        $seasonalVolume = match ($day->month) {
            12, 1, 2 => 1.15,
            6, 7, 8 => 0.88,
            default => 1.0,
        };
        $weekdayVolume = $day->isWeekend() ? 0.45 : 1.22;
        $expected = $prescriptionsThisYear / 365 * $seasonalVolume * $weekdayVolume;

        return max(1, (int) floor($expected + random_int(0, 100) / 100));
    }

    private function writePrescription(CarbonImmutable $day, int $registeredPatients, array $cumulativeWeights): void
    {
        $isRepeat = $this->recentPrescriptions !== [] && random_int(1, 100) <= self::PERCENT_OF_REPEAT_PRESCRIPTIONS;

        if ($isRepeat) {
            $earlier = $this->recentPrescriptions[array_rand($this->recentPrescriptions)];
            $patientId = $earlier['patient_id'];
            $medicationId = $earlier['medication_id'];
            $physicianId = $earlier['physician_id'];
            $dose = $earlier['dose'];
        } else {
            $patient = $this->patients[random_int(0, $registeredPatients - 1)];
            $patientId = $patient['id'];
            $medicationId = $this->formulary[$this->pickWeightedIndex($cumulativeWeights)]['id'];
            $physicianId = $patient['primaryPhysicianId'] !== null && random_int(1, 100) <= self::PERCENT_BY_PRIMARY_PHYSICIAN
                ? $patient['primaryPhysicianId']
                : $this->prescriberIds[array_rand($this->prescriberIds)];
            $dose = self::DOSES[array_rand(self::DOSES)];
        }

        $uniqueKey = "{$physicianId}|{$patientId}|{$medicationId}|{$day->toDateString()}";

        if (isset($this->writtenPrescriptions[$uniqueKey])) {
            return;
        }

        $writtenAt = $day->setTime(random_int(8, 18), random_int(0, 59), random_int(0, 59));
        $prescription = [
            'id' => Str::lower((string) Str::ulid($writtenAt)),
            'physician_id' => $physicianId,
            'patient_id' => $patientId,
            'medication_id' => $medicationId,
            'prescribedDate' => $day->toDateString(),
            'dose' => $dose,
            'created_at' => $writtenAt,
            'updated_at' => $writtenAt,
        ];

        $this->writtenPrescriptions[$uniqueKey] = $prescription;
        $this->recentPrescriptions[] = $prescription;

        if (count($this->recentPrescriptions) > 5000) {
            array_shift($this->recentPrescriptions);
        }
    }

    private function pickWeightedIndex(array $cumulativeWeights): int
    {
        $target = (random_int(0, PHP_INT_MAX - 1) / PHP_INT_MAX) * end($cumulativeWeights);
        $low = 0;
        $high = count($cumulativeWeights) - 1;

        while ($low < $high) {
            $middle = intdiv($low + $high, 2);

            if ($cumulativeWeights[$middle] < $target) {
                $low = $middle + 1;
            } else {
                $high = $middle;
            }
        }

        return $low;
    }
}
