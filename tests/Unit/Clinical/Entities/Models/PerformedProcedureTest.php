<?php

use App\Clinical\Entities\Models\PerformedProcedure;
use App\Clinical\Entities\Models\Procedure;
use App\Clinical\Entities\Models\Stay;
use App\Hospital\Entities\Models\Room;
use App\Hospital\Entities\RoomType;
use App\Patient\Entities\Models\Patient;
use App\Staff\Entities\Models\Nurse;
use App\Staff\Entities\Models\Physician;
use App\Staff\Entities\NursePosition;
use App\Staff\Entities\PhysicianPosition;
use Illuminate\Database\QueryException;

/**
 * @return array{patient_id: string, procedure_id: string, stay_id: string, physician_id: string, nurse_id: string}
 */
function performedProcedureParties(): array
{
    $patient = Patient::create(['patientID' => 1, 'name' => 'Harvey Corman']);
    $room = Room::create(['roomID' => 1, 'roomType' => RoomType::Single]);

    return [
        'patient_id' => $patient->id,
        'procedure_id' => Procedure::create(['procID' => 1, 'name' => 'Appendectomy'])->id,
        'stay_id' => Stay::create(['stayID' => 1, 'patient_id' => $patient->id, 'room_id' => $room->id, 'startDate' => '2026-09-01'])->id,
        'physician_id' => Physician::create(['physicianID' => 1, 'name' => 'Chris Turk', 'position' => PhysicianPosition::Surgeon])->id,
        'nurse_id' => Nurse::create(['nurseID' => 1, 'name' => 'Carla Espinosa', 'position' => NursePosition::HeadNurse])->id,
    ];
}

it('returns the patient, procedure, stay, physician and nurse', function () {
    $parties = performedProcedureParties();

    $performedProcedure = PerformedProcedure::create([...$parties, 'procDate' => '2026-09-02']);

    expect($performedProcedure->patient->id)->toBe($parties['patient_id']);
    expect($performedProcedure->procedure->id)->toBe($parties['procedure_id']);
    expect($performedProcedure->stay->id)->toBe($parties['stay_id']);
    expect($performedProcedure->physician->id)->toBe($parties['physician_id']);
    expect($performedProcedure->nurse->id)->toBe($parties['nurse_id']);
});

it('generates a ulid primary key', function () {
    $performedProcedure = PerformedProcedure::create([...performedProcedureParties(), 'procDate' => '2026-09-02']);

    expect($performedProcedure->getKey())->toBeString()->toHaveLength(26);
});

it('reads the procedure date back as a date', function () {
    $performedProcedure = PerformedProcedure::create([...performedProcedureParties(), 'procDate' => '2026-09-02']);

    expect(PerformedProcedure::find($performedProcedure->id)->procDate->toDateString())->toBe('2026-09-02');
});

it('rejects the same procedure for the same patient and stay twice on the same day', function () {
    $parties = performedProcedureParties();
    PerformedProcedure::create([...$parties, 'procDate' => '2026-09-02']);

    PerformedProcedure::create([...$parties, 'procDate' => '2026-09-02']);
})->throws(QueryException::class);

it('blocks permanently deleting a record the procedure refers to', function (string $column, string $model) {
    $parties = performedProcedureParties();
    PerformedProcedure::create([...$parties, 'procDate' => '2026-09-02']);

    $model::find($parties[$column])->forceDelete();
})->with([
    'patient' => ['patient_id', Patient::class],
    'procedure' => ['procedure_id', Procedure::class],
    'stay' => ['stay_id', Stay::class],
    'physician' => ['physician_id', Physician::class],
    'nurse' => ['nurse_id', Nurse::class],
])->throws(QueryException::class);

it('keeps the row when a performed procedure is deleted', function () {
    $performedProcedure = PerformedProcedure::create([...performedProcedureParties(), 'procDate' => '2026-09-02']);

    $performedProcedure->delete();

    $this->assertSoftDeleted($performedProcedure);
});
