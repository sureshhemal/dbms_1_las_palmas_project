<?php

use App\Clinical\Entities\Models\Medication;
use App\Clinical\Entities\Models\Prescription;
use App\Patient\Entities\Models\Patient;
use App\Staff\Entities\Models\Physician;
use App\Staff\Entities\PhysicianPosition;
use Illuminate\Database\QueryException;

function prescriptionParties(): array
{
    return [
        'physician_id' => Physician::create(['physicianID' => 1, 'name' => 'John Dorian', 'position' => PhysicianPosition::Resident])->id,
        'patient_id' => Patient::create(['patientID' => 1, 'name' => 'Harvey Corman'])->id,
        'medication_id' => Medication::create(['medID' => 1, 'name' => 'Amoxicillin'])->id,
    ];
}

it('returns the physician, patient and medication', function () {
    $parties = prescriptionParties();

    $prescription = Prescription::create([...$parties, 'prescribedDate' => '2026-09-01', 'dose' => '500mg']);

    expect($prescription->physician->id)->toBe($parties['physician_id']);
    expect($prescription->patient->id)->toBe($parties['patient_id']);
    expect($prescription->medication->id)->toBe($parties['medication_id']);
});

it('generates a ulid primary key', function () {
    $prescription = Prescription::create([...prescriptionParties(), 'prescribedDate' => '2026-09-01', 'dose' => '500 mg twice daily']);

    expect($prescription->getKey())->toBeString()->toHaveLength(26);
});

it('reads the prescribed date back as a date', function () {
    $prescription = Prescription::create([...prescriptionParties(), 'prescribedDate' => '2026-09-01', 'dose' => '500 mg twice daily']);

    expect(Prescription::find($prescription->id)->prescribedDate->toDateString())->toBe('2026-09-01');
});

it('rejects the same prescription twice on the same day', function () {
    $parties = prescriptionParties();
    Prescription::create([...$parties, 'prescribedDate' => '2026-09-01', 'dose' => '500 mg twice daily']);

    Prescription::create([...$parties, 'prescribedDate' => '2026-09-01', 'dose' => '500 mg twice daily']);
})->throws(QueryException::class);

it('allows the same prescription on a different day', function () {
    $parties = prescriptionParties();
    Prescription::create([...$parties, 'prescribedDate' => '2026-09-01', 'dose' => '500 mg twice daily']);

    Prescription::create([...$parties, 'prescribedDate' => '2026-09-08', 'dose' => '500 mg twice daily']);

    $this->assertDatabaseCount('Prescribes', 2);
});

it('removes the prescriptions when the patient is permanently deleted', function () {
    $parties = prescriptionParties();
    Prescription::create([...$parties, 'prescribedDate' => '2026-09-01', 'dose' => '500 mg twice daily']);

    Patient::find($parties['patient_id'])->forceDelete();

    $this->assertDatabaseEmpty('Prescribes');
});

it('blocks permanently deleting a physician who has prescribed', function () {
    $parties = prescriptionParties();
    Prescription::create([...$parties, 'prescribedDate' => '2026-09-01', 'dose' => '500 mg twice daily']);

    Physician::find($parties['physician_id'])->forceDelete();
})->throws(QueryException::class);

it('blocks permanently deleting a prescribed medication', function () {
    $parties = prescriptionParties();
    Prescription::create([...$parties, 'prescribedDate' => '2026-09-01', 'dose' => '500 mg twice daily']);

    Medication::find($parties['medication_id'])->forceDelete();
})->throws(QueryException::class);

it('keeps the row when a prescription is deleted', function () {
    $prescription = Prescription::create([...prescriptionParties(), 'prescribedDate' => '2026-09-01', 'dose' => '500 mg twice daily']);

    $prescription->delete();

    $this->assertSoftDeleted($prescription);
});

it('rejects a prescription without a dose', function () {
    Prescription::create([...prescriptionParties(), 'prescribedDate' => '2026-09-01']);
})->throws(QueryException::class);
