<?php

use App\Patient\Entities\Models\Patient;
use App\Staff\Entities\Models\Physician;
use App\Staff\Entities\PhysicianPosition;
use Illuminate\Database\QueryException;
use Illuminate\Database\UniqueConstraintViolationException;

it('generates a ulid primary key', function () {
    $patient = Patient::create(['patientID' => 1, 'name' => 'Harvey Corman']);

    expect($patient->getKey())->toBeString()->toHaveLength(26);
});

it('allows a patient with only a name and patient id', function () {
    $patient = Patient::create(['patientID' => 2, 'name' => 'Harvey Corman']);

    $this->assertDatabaseHas('Patient', [
        'id' => $patient->id,
        'ssn' => null,
        'address' => null,
        'dob' => null,
        'phone' => null,
        'insuranceNumber' => null,
        'primary_physician_id' => null,
    ]);
});

it('reads the date of birth back as a date', function () {
    $patient = Patient::create(['patientID' => 3, 'name' => 'Harvey Corman', 'dob' => '1970-03-15']);

    expect(Patient::find($patient->id)->dob->toDateString())->toBe('1970-03-15');
});

it('allows patients to share an insurance number', function () {
    Patient::create(['patientID' => 4, 'name' => 'Harvey Corman', 'insuranceNumber' => 68421879]);

    Patient::create(['patientID' => 5, 'name' => 'Mary Corman', 'insuranceNumber' => 68421879]);

    $this->assertDatabaseCount('Patient', 2);
});

it('returns the primary physician', function () {
    $physician = Physician::create(['physicianID' => 1, 'name' => 'John Dorian', 'position' => PhysicianPosition::Resident]);
    $patient = Patient::create(['patientID' => 6, 'name' => 'Harvey Corman', 'primary_physician_id' => $physician->id]);

    expect($patient->primaryPhysician->is($physician))->toBeTrue();
});

it('rejects a primary physician that does not exist', function () {
    Patient::create(['patientID' => 7, 'name' => 'Harvey Corman', 'primary_physician_id' => (string) str()->ulid()]);
})->throws(QueryException::class);

it('clears the primary physician when the physician is permanently deleted', function () {
    $physician = Physician::create(['physicianID' => 2, 'name' => 'John Dorian', 'position' => PhysicianPosition::Resident]);
    $patient = Patient::create(['patientID' => 8, 'name' => 'Harvey Corman', 'primary_physician_id' => $physician->id]);

    $physician->forceDelete();

    expect($patient->fresh()->primary_physician_id)->toBeNull();
});

it('keeps the row when a patient is deleted', function () {
    $patient = Patient::create(['patientID' => 9, 'name' => 'Harvey Corman']);

    $patient->delete();

    $this->assertSoftDeleted($patient);
});

it('rejects a duplicate patientID', function () {
    Patient::create(['patientID' => 1, 'name' => 'Harvey Corman']);

    Patient::create(['patientID' => 1, 'name' => 'Harvey Corman']);
})->throws(UniqueConstraintViolationException::class);

it('assigns the next patientID when none is given', function () {
    Patient::create(['patientID' => 900000, 'name' => 'Harvey Corman']);

    $patient = Patient::create(['name' => 'Harvey Corman']);

    expect($patient->patientID)->toBe(900001);
    expect(Patient::find($patient->id)->patientID)->toBe(900001);
});

it('keeps the leading zeros of the ssn', function () {
    $patient = Patient::create(['patientID' => 1, 'name' => 'Harvey Corman', 'ssn' => '012-34-5678']);

    expect(Patient::find($patient->id)->ssn)->toBe('012-34-5678');
});

it('calculates the age in whole years on the hospital day', function (string $dob, string $today, int $expectedAge) {
    $this->travelTo($today);

    $patient = Patient::factory()->create(['dob' => $dob]);

    expect($patient->age)->toBe($expectedAge);
})->with([
    'the day before the birthday' => ['1975-04-12', '2026-04-11 12:00:00', 50],
    'on the birthday' => ['1975-04-12', '2026-04-12 12:00:00', 51],
    'a baby under a year old' => ['2026-01-20', '2026-10-15 12:00:00', 0],
]);

it('uses the hospital time zone to decide which day it is', function () {
    config(['app.hospital_timezone' => 'Asia/Colombo']);
    $this->travelTo('2026-04-11 20:00:00');

    $patient = Patient::factory()->create(['dob' => '1975-04-12']);

    expect($patient->age)->toBe(51);
});

it('has no age without a date of birth', function () {
    $patient = Patient::factory()->create(['dob' => null]);

    expect($patient->age)->toBeNull();
});
