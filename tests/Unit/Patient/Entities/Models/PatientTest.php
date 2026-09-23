<?php

use App\Patient\Entities\Models\Patient;
use App\Staff\Entities\Models\Physician;
use App\Staff\Entities\PhysicianPosition;
use Illuminate\Database\QueryException;

it('generates a ulid primary key', function () {
    $patient = Patient::create(['name' => 'Harvey Corman']);

    expect($patient->getKey())->toBeString()->toHaveLength(26);
});

it('allows a patient with only a name', function () {
    $patient = Patient::create(['name' => 'Harvey Corman']);

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
    $patient = Patient::create(['name' => 'Harvey Corman', 'dob' => '1970-03-15']);

    expect(Patient::find($patient->id)->dob->toDateString())->toBe('1970-03-15');
});

it('allows patients to share an insurance number', function () {
    Patient::create(['name' => 'Harvey Corman', 'insuranceNumber' => 68421879]);

    Patient::create(['name' => 'Mary Corman', 'insuranceNumber' => 68421879]);

    $this->assertDatabaseCount('Patient', 2);
});

it('returns the primary physician', function () {
    $physician = Physician::create(['name' => 'John Dorian', 'position' => PhysicianPosition::Resident]);
    $patient = Patient::create(['name' => 'Harvey Corman', 'primary_physician_id' => $physician->id]);

    expect($patient->primaryPhysician->is($physician))->toBeTrue();
});

it('rejects a primary physician that does not exist', function () {
    Patient::create(['name' => 'Harvey Corman', 'primary_physician_id' => (string) str()->ulid()]);
})->throws(QueryException::class);

it('clears the primary physician when the physician is permanently deleted', function () {
    $physician = Physician::create(['name' => 'John Dorian', 'position' => PhysicianPosition::Resident]);
    $patient = Patient::create(['name' => 'Harvey Corman', 'primary_physician_id' => $physician->id]);

    $physician->forceDelete();

    expect($patient->fresh()->primary_physician_id)->toBeNull();
});

it('keeps the row when a patient is deleted', function () {
    $patient = Patient::create(['name' => 'Harvey Corman']);

    $patient->delete();

    $this->assertSoftDeleted($patient);
});
