<?php

use App\Clinical\Entities\Models\Appointment;
use App\Patient\Entities\Models\Patient;
use App\Staff\Entities\Models\Nurse;
use App\Staff\Entities\Models\Physician;
use App\Staff\Entities\NursePosition;
use App\Staff\Entities\PhysicianPosition;
use Illuminate\Database\QueryException;

/**
 * @return array{patient_id: string, nurse_id: string, physician_id: string}
 */
function appointmentParties(): array
{
    return [
        'patient_id' => Patient::create(['name' => 'Harvey Corman'])->id,
        'nurse_id' => Nurse::create(['name' => 'Carla Espinosa', 'position' => NursePosition::Nurse])->id,
        'physician_id' => Physician::create(['name' => 'John Dorian', 'position' => PhysicianPosition::Resident])->id,
    ];
}

it('returns the patient, nurse and physician', function () {
    $parties = appointmentParties();

    $appointment = Appointment::create([...$parties, 'startDateTime' => '2026-09-01 09:00:00']);

    expect($appointment->patient->id)->toBe($parties['patient_id']);
    expect($appointment->nurse->id)->toBe($parties['nurse_id']);
    expect($appointment->physician->id)->toBe($parties['physician_id']);
});

it('generates a ulid primary key', function () {
    $appointment = Appointment::create([...appointmentParties(), 'startDateTime' => '2026-09-01 09:00:00']);

    expect($appointment->getKey())->toBeString()->toHaveLength(26);
});

it('allows an appointment without an end time', function () {
    $appointment = Appointment::create([...appointmentParties(), 'startDateTime' => '2026-09-01 09:00:00']);

    expect(Appointment::find($appointment->id)->endDateTime)->toBeNull();
});

it('reads the start and end back as date times', function () {
    $appointment = Appointment::create([...appointmentParties(), 'startDateTime' => '2026-09-01 09:00:00', 'endDateTime' => '2026-09-01 09:30:00']);

    $appointment = Appointment::find($appointment->id);
    expect($appointment->startDateTime->toDateTimeString())->toBe('2026-09-01 09:00:00');
    expect($appointment->endDateTime->toDateTimeString())->toBe('2026-09-01 09:30:00');
});

it('rejects an end time that is not after the start time', function (string $endDateTime) {
    Appointment::create([...appointmentParties(), 'startDateTime' => '2026-09-01 09:00:00', 'endDateTime' => $endDateTime]);
})->with([
    'before the start time' => '2026-09-01 08:30:00',
    'at the start time' => '2026-09-01 09:00:00',
])->throws(QueryException::class);

it('removes the appointments when the patient is permanently deleted', function () {
    $parties = appointmentParties();
    Appointment::create([...$parties, 'startDateTime' => '2026-09-01 09:00:00']);

    Patient::find($parties['patient_id'])->forceDelete();

    $this->assertDatabaseEmpty('Appointment');
});

it('blocks permanently deleting staff who have appointments', function (string $column, string $model) {
    $parties = appointmentParties();
    Appointment::create([...$parties, 'startDateTime' => '2026-09-01 09:00:00']);

    $model::find($parties[$column])->forceDelete();
})->with([
    'nurse' => ['nurse_id', Nurse::class],
    'physician' => ['physician_id', Physician::class],
])->throws(QueryException::class);

it('keeps the row when an appointment is deleted', function () {
    $appointment = Appointment::create([...appointmentParties(), 'startDateTime' => '2026-09-01 09:00:00']);

    $appointment->delete();

    $this->assertSoftDeleted($appointment);
});
