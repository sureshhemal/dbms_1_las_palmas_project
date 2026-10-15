<?php

namespace Database\Seeders;

use App\Hospital\Entities\HospitalCalendar;
use App\Patient\Entities\Models\Patient;
use App\Staff\Entities\Models\Physician;
use Carbon\CarbonImmutable;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class PatientSeeder extends Seeder
{
    private const int NUMBER_OF_PATIENTS = 10000;

    private const int ROWS_PER_INSERT = 500;

    private const int PERCENT_OF_HOUSEHOLDS_WITHOUT_INSURANCE = 12;

    private const int PERCENT_OF_PATIENTS_WITHOUT_PRIMARY_PHYSICIAN = 10;

    private const int YEARS_OF_REGISTRATIONS = 5;

    public function run(): void
    {
        if (Patient::query()->withTrashed()->exists()) {
            return;
        }

        $primaryPhysicianIds = Physician::query()->whereCapableOfBeingPrimaryPhysicianBasedOnPosition()->pluck('id')->all();
        $today = HospitalCalendar::today();
        $patients = [];

        while (count($patients) < self::NUMBER_OF_PATIENTS) {
            array_push($patients, ...$this->household($primaryPhysicianIds, $today));
        }

        collect(array_slice($patients, 0, self::NUMBER_OF_PATIENTS))
            ->sortBy('created_at')
            ->chunk(self::ROWS_PER_INSERT)
            ->each(fn ($rows) => DB::table('Patient')->insert($rows->values()->all()));
    }

    private function household(array $primaryPhysicianIds, CarbonImmutable $today): array
    {
        $surname = fake()->lastName();
        $address = fake()->streetAddress().', '.fake()->city();
        $insuranceNumber = random_int(1, 100) <= self::PERCENT_OF_HOUSEHOLDS_WITHOUT_INSURANCE
            ? null
            : fake()->unique()->numberBetween(10000000, 99999999);
        $phone = fake()->numerify('(###) ###-####');

        return array_map(
            fn (): array => $this->patient($surname, $address, $insuranceNumber, $phone, $primaryPhysicianIds, $today),
            range(1, fake()->randomElement([1, 1, 1, 2, 2, 3, 4, 5])),
        );
    }

    private function patient(string $surname, string $address, ?int $insuranceNumber, string $phone, array $primaryPhysicianIds, CarbonImmutable $today): array
    {
        $dob = CarbonImmutable::instance(fake()->dateTimeBetween('-95 years', $today->subDay()))->startOfDay();
        $isBaby = $dob->greaterThan($today->subYear());
        $registeredAt = CarbonImmutable::instance(fake()->dateTimeBetween('-'.self::YEARS_OF_REGISTRATIONS.' years', 'now'));

        return [
            'id' => Str::lower((string) Str::ulid($registeredAt)),
            'name' => fake()->firstName().' '.$surname,
            'ssn' => $isBaby || random_int(1, 100) <= 3 ? null : fake()->numerify('#########'),
            'address' => random_int(1, 100) <= 2 ? null : $address,
            'dob' => random_int(1, 100) === 1 ? null : $dob->toDateString(),
            'phone' => random_int(1, 100) <= 3 ? null : $phone,
            'insuranceNumber' => $insuranceNumber,
            'primary_physician_id' => $primaryPhysicianIds === [] || random_int(1, 100) <= self::PERCENT_OF_PATIENTS_WITHOUT_PRIMARY_PHYSICIAN
                ? null
                : $primaryPhysicianIds[array_rand($primaryPhysicianIds)],
            'created_at' => $registeredAt,
            'updated_at' => $registeredAt,
        ];
    }
}
