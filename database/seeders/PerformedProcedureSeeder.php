<?php

namespace Database\Seeders;

use App\Clinical\Entities\Models\PerformedProcedure;
use App\Hospital\Entities\HospitalCalendar;
use App\Staff\Entities\PhysicianPosition;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use stdClass;

class PerformedProcedureSeeder extends Seeder
{
    private const int PERCENT_OF_PSYCHIATRIC_STAYS = 15;

    private const int PERCENT_OF_SURGICAL_STAYS = 45;

    private const int PERCENT_OF_TESTS_BY_PRIMARY_PHYSICIAN = 30;

    private const int ROWS_PER_INSERT = 1000;

    private const int SMALLEST_REPERTOIRE = 4;

    private const array DIAGNOSTIC_WORDS = ['X-Ray', 'CT ', 'MRI', 'Ultrasound', 'Scan', 'Test', 'Panel', 'Count', 'Culture', 'Level', 'Urinalysis', 'Electrocardiogram', 'Echocardiogram', 'Doppler', 'Mammogram', 'Spirometry', 'Blood Gas', 'Monitoring', 'Crossmatch', 'Screen', 'Hemoglobin', 'Protein', 'Antigen', 'D-Dimer', 'Troponin'];

    private const array PSYCHIATRIC_WORDS = ['Psychiatric', 'Psychotherapy', 'Psychological', 'Neuropsychological', 'Behavior', 'Electroconvulsive', 'Transcranial', 'Ketamine', 'Esketamine', 'Detoxification', 'Antipsychotic', 'Clozapine', 'Suicide', 'Crisis', 'Family Therapy', 'Group Psychotherapy', 'Art Therapy', 'Psychoeducation', 'Peer Support', 'Substance Use', 'Eating Disorder', 'ADHD', 'Autism', 'Partial Hospitalization', 'Intensive Outpatient', 'Mental', 'Medication Assisted', 'Forensic'];

    private const array SURGICAL_WORDS = ['ectomy', 'otomy', 'ostomy', 'plasty', 'Repair', 'Replacement', 'Fixation', 'Surgery', 'Excision', 'Amputation', 'Cesarean', 'Bypass', 'Ligation', 'Release', 'Arthroscopy', 'Graft', 'Debridement', 'Reduction', 'Resection', 'Laparoscopy', 'Laparotomy', 'Fundoplication', 'Ablation', 'Creation', 'Delivery', 'Kyphoplasty', 'Bunionectomy', 'Myomectomy'];

    private array $diagnostics = [];

    private array $psychiatricProcedures = [];

    private array $surgeries = [];

    private array $treatments = [];

    private array $physiciansByRole = [];

    private array $mainRepertoireOf = [];

    private array $diagnosticRepertoireOf = [];

    private array $nurseIds = [];

    private array $nursesOnCallByDay = [];

    private array $primaryPhysicianOfPatient = [];

    private array $rows = [];

    public function run(): void
    {
        if (PerformedProcedure::query()->withoutGlobalScope(SoftDeletingScope::class)->exists()) {
            return;
        }

        $this->groupProcedures();
        $this->groupPhysicians();
        $this->shareOutRepertoires();
        $this->nurseIds = DB::table('Nurse')->whereNull('deleted_at')->pluck('id')->all();
        $this->nursesOnCallByDay = $this->nursesOnCallByDay();
        $this->primaryPhysicianOfPatient = DB::table('Patient')->whereNotNull('primary_physician_id')->pluck('primary_physician_id', 'id')->all();

        if ($this->nurseIds === [] || $this->physiciansByRole['general'] === [] || $this->diagnostics === []) {
            return;
        }

        $today = HospitalCalendar::today();

        DB::table('Stay')->whereNull('deleted_at')->orderBy('startDate')->select(['id', 'patient_id', 'startDate', 'endDate'])
            ->chunk(2000, function ($stays) use ($today): void {
                foreach ($stays as $stay) {
                    $this->performProceduresDuring($stay, $today);
                }
            });

        collect(array_values($this->rows))
            ->chunk(self::ROWS_PER_INSERT)
            ->each(fn ($rows) => DB::table('Undergoes')->insert($rows->values()->all()));
    }

    private function groupProcedures(): void
    {
        foreach (DB::table('Procedure')->whereNull('deleted_at')->get(['id', 'name']) as $procedure) {
            match (true) {
                Str::contains($procedure->name, self::PSYCHIATRIC_WORDS) => $this->psychiatricProcedures[] = $procedure->id,
                Str::contains($procedure->name, self::DIAGNOSTIC_WORDS) => $this->diagnostics[] = $procedure->id,
                Str::contains($procedure->name, self::SURGICAL_WORDS) => $this->surgeries[] = $procedure->id,
                default => $this->treatments[] = $procedure->id,
            };
        }
    }

    private function groupPhysicians(): void
    {
        $physicians = DB::table('Physician')->whereNull('deleted_at')
            ->whereIn('position', array_map(fn (PhysicianPosition $position): string => $position->value, PhysicianPosition::capableOfPrescribing()))
            ->get(['id', 'position']);

        $this->physiciansByRole = [
            'psychiatric' => $physicians->where('position', PhysicianPosition::Psychiatrist->value)->pluck('id')->all(),
            'surgical' => $physicians->where('position', PhysicianPosition::Surgeon->value)->pluck('id')->all(),
            'general' => $physicians->whereNotIn('position', [PhysicianPosition::Psychiatrist->value, PhysicianPosition::Surgeon->value])->pluck('id')->all(),
        ];
    }

    private function shareOutRepertoires(): void
    {
        $this->mainRepertoireOf = [
            ...$this->shareOut($this->psychiatricProcedures, $this->physiciansByRole['psychiatric']),
            ...$this->shareOut($this->surgeries, $this->physiciansByRole['surgical']),
            ...$this->shareOut($this->treatments !== [] ? $this->treatments : $this->diagnostics, $this->physiciansByRole['general']),
        ];
        $this->diagnosticRepertoireOf = $this->shareOut($this->diagnostics, [...$this->physiciansByRole['surgical'], ...$this->physiciansByRole['general']]);
    }

    private function shareOut(array $procedureIds, array $physicianIds): array
    {
        if ($procedureIds === [] || $physicianIds === []) {
            return [];
        }

        shuffle($procedureIds);
        $procedureCount = count($procedureIds);
        $physicianCount = count($physicianIds);
        $repertoireSize = min($procedureCount, max(self::SMALLEST_REPERTOIRE, (int) ceil($procedureCount / $physicianCount) + 1));
        $repertoires = [];

        foreach (array_values($physicianIds) as $index => $physicianId) {
            $firstProcedure = intdiv($index * $procedureCount, $physicianCount);
            $repertoires[$physicianId] = array_map(
                fn (int $offset): string => $procedureIds[($firstProcedure + $offset) % $procedureCount],
                range(0, $repertoireSize - 1),
            );
        }

        return $repertoires;
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

    private function performProceduresDuring(stdClass $stay, CarbonImmutable $today): void
    {
        $admittedOn = CarbonImmutable::parse($stay->startDate);
        $lastDay = $stay->endDate === null ? $today : CarbonImmutable::parse($stay->endDate);
        $lengthInDays = max(0, (int) $admittedOn->diffInDays($lastDay));
        $mainPhysicianId = $this->pick($this->physiciansByRole[$this->mainRoleForAStay()]);

        $this->perform($stay, $this->pick($this->mainRepertoireOf[$mainPhysicianId]), $admittedOn->addDays(random_int(0, min(1, $lengthInDays))), $mainPhysicianId);

        foreach (range(1, random_int(1, 3)) as $ignored) {
            $primaryPhysicianId = $this->primaryPhysicianOfPatient[$stay->patient_id] ?? null;
            $physicianId = $primaryPhysicianId !== null && isset($this->diagnosticRepertoireOf[$primaryPhysicianId]) && random_int(1, 100) <= self::PERCENT_OF_TESTS_BY_PRIMARY_PHYSICIAN
                ? $primaryPhysicianId
                : $this->pick(array_keys($this->diagnosticRepertoireOf));

            $this->perform($stay, $this->pick($this->diagnosticRepertoireOf[$physicianId]), $admittedOn->addDays(random_int(0, $lengthInDays)), $physicianId);
        }
    }

    private function mainRoleForAStay(): string
    {
        $chance = random_int(1, 100);

        return match (true) {
            $chance <= self::PERCENT_OF_PSYCHIATRIC_STAYS && $this->psychiatricProcedures !== [] && $this->physiciansByRole['psychiatric'] !== [] => 'psychiatric',
            $chance <= self::PERCENT_OF_PSYCHIATRIC_STAYS + self::PERCENT_OF_SURGICAL_STAYS && $this->surgeries !== [] && $this->physiciansByRole['surgical'] !== [] => 'surgical',
            default => 'general',
        };
    }

    private function perform(stdClass $stay, string $procedureId, CarbonImmutable $day, string $physicianId): void
    {
        $uniqueKey = "{$stay->patient_id}|{$procedureId}|{$stay->id}|{$day->toDateString()}";

        if (isset($this->rows[$uniqueKey])) {
            return;
        }

        $performedAt = $day->setTime(random_int(7, 19), random_int(0, 59));
        $this->rows[$uniqueKey] = [
            'id' => Str::lower((string) Str::ulid($performedAt)),
            'patient_id' => $stay->patient_id,
            'procedure_id' => $procedureId,
            'stay_id' => $stay->id,
            'physician_id' => $physicianId,
            'nurse_id' => $this->pick($this->nursesOnCallByDay[$day->toDateString()] ?? $this->nurseIds),
            'procDate' => $day->toDateString(),
            'created_at' => $performedAt,
            'updated_at' => $performedAt,
        ];
    }

    private function pick(array $ids): string
    {
        return $ids[array_rand($ids)];
    }
}
