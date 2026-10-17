<?php

use App\Clinical\Entities\Models\Appointment;
use App\Patient\Entities\Models\Patient;
use App\Staff\Entities\Models\Nurse;
use App\Staff\Entities\Models\Physician;
use App\Staff\Entities\NursePosition;
use App\Staff\Entities\PhysicianPosition;
use Illuminate\Database\QueryException;
use Illuminate\Database\UniqueConstraintViolationException;

function appointmentParties(): array
{
    return [
        'patient_id' => Patient::create(['patientID' => 1, 'name' => 'Harvey Corman'])->id,
        'nurse_id' => Nurse::create(['nurseID' => 1, 'name' => 'Carla Espinosa', 'position' => NursePosition::Nurse])->id,
        'physician_id' => Physician::create(['physicianID' => 1, 'name' => 'John Dorian', 'position' => PhysicianPosition::Resident])->id,
    ];
}

it('returns the patient, nurse and physician', function () {
    $parties = appointmentParties();

    $appointment = Appointment::create(['appID' => 1, ...$parties, 'startDateTime' => '2026-09-01 09:00:00', 'endDateTime' => '2026-09-01 09:30:00']);

    expect($appointment->patient->id)->toBe($parties['patient_id']);
    expect($appointment->nurse->id)->toBe($parties['nurse_id']);
    expect($appointment->physician->id)->toBe($parties['physician_id']);
});

it('generates a ulid primary key', function () {
    $appointment = Appointment::create(['appID' => 2, ...appointmentParties(), 'startDateTime' => '2026-09-01 09:00:00', 'endDateTime' => '2026-09-01 09:30:00']);

    expect($appointment->getKey())->toBeString()->toHaveLength(26);
});

it('requires an end time', function () {
    Appointment::create(['appID' => 3, ...appointmentParties(), 'startDateTime' => '2026-09-01 09:00:00']);
})->throws(QueryException::class, "Field 'endDateTime' doesn't have a default value");

it('reads the start and end back as date times', function () {
    $appointment = Appointment::create(['appID' => 4, ...appointmentParties(), 'startDateTime' => '2026-09-01 09:00:00', 'endDateTime' => '2026-09-01 09:30:00']);

    $appointment = Appointment::find($appointment->id);
    expect($appointment->startDateTime->toDateTimeString())->toBe('2026-09-01 09:00:00');
    expect($appointment->endDateTime->toDateTimeString())->toBe('2026-09-01 09:30:00');
});

it('rejects an end time that is not after the start time', function (string $endDateTime) {
    Appointment::create(['appID' => 5, ...appointmentParties(), 'startDateTime' => '2026-09-01 09:00:00', 'endDateTime' => $endDateTime]);
})->with([
    'before the start time' => '2026-09-01 08:30:00',
    'at the start time' => '2026-09-01 09:00:00',
])->throws(QueryException::class);

it('removes the appointments when the patient is permanently deleted', function () {
    $parties = appointmentParties();
    Appointment::create(['appID' => 6, ...$parties, 'startDateTime' => '2026-09-01 09:00:00', 'endDateTime' => '2026-09-01 09:30:00']);

    Patient::find($parties['patient_id'])->forceDelete();

    $this->assertDatabaseEmpty('Appointment');
});

it('blocks permanently deleting staff who have appointments', function (string $column, string $model) {
    $parties = appointmentParties();
    Appointment::create(['appID' => 7, ...$parties, 'startDateTime' => '2026-09-01 09:00:00', 'endDateTime' => '2026-09-01 09:30:00']);

    $model::find($parties[$column])->forceDelete();
})->with([
    'nurse' => ['nurse_id', Nurse::class],
    'physician' => ['physician_id', Physician::class],
])->throws(QueryException::class);

it('keeps the row when an appointment is deleted', function () {
    $appointment = Appointment::create(['appID' => 8, ...appointmentParties(), 'startDateTime' => '2026-09-01 09:00:00', 'endDateTime' => '2026-09-01 09:30:00']);

    $appointment->delete();

    $this->assertSoftDeleted($appointment);
});

it('rejects a duplicate appID', function () {
    $parties = appointmentParties();
    Appointment::create(['appID' => 1, ...$parties, 'startDateTime' => '2026-09-01 09:00:00', 'endDateTime' => '2026-09-01 09:30:00']);

    Appointment::create(['appID' => 1, ...$parties, 'startDateTime' => '2026-09-01 09:00:00', 'endDateTime' => '2026-09-01 09:30:00']);
})->throws(UniqueConstraintViolationException::class);

it('assigns the next appID when none is given', function () {
    $parties = appointmentParties();
    Appointment::create(['appID' => 900000, ...$parties, 'startDateTime' => '2026-09-01 09:00:00', 'endDateTime' => '2026-09-01 09:30:00']);

    $appointment = Appointment::create([...$parties, 'startDateTime' => '2026-09-01 09:00:00', 'endDateTime' => '2026-09-01 09:30:00']);

    expect($appointment->appID)->toBe(900001);
    expect(Appointment::find($appointment->id)->appID)->toBe(900001);
});
