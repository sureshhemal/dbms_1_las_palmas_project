<?php

use App\Clinical\Entities\Models\Stay;
use App\Hospital\Entities\Models\Room;
use App\Hospital\Entities\RoomType;
use App\Patient\Entities\Models\Patient;
use Illuminate\Database\QueryException;
use Illuminate\Database\UniqueConstraintViolationException;

function stayParties(): array
{
    return [
        'patient_id' => Patient::create(['patientID' => 1, 'name' => 'Harvey Corman'])->id,
        'room_id' => Room::create(['roomID' => 1, 'roomType' => RoomType::Single])->id,
    ];
}

it('returns the patient and room', function () {
    $parties = stayParties();

    $stay = Stay::create(['stayID' => 1, ...$parties, 'startDate' => '2026-09-01']);

    expect($stay->patient->id)->toBe($parties['patient_id']);
    expect($stay->room->id)->toBe($parties['room_id']);
});

it('generates a ulid primary key', function () {
    $stay = Stay::create(['stayID' => 2, ...stayParties(), 'startDate' => '2026-09-01']);

    expect($stay->getKey())->toBeString()->toHaveLength(26);
});

it('allows a stay without an end date', function () {
    $stay = Stay::create(['stayID' => 3, ...stayParties(), 'startDate' => '2026-09-01']);

    expect(Stay::find($stay->id)->endDate)->toBeNull();
});

it('reads the dates back as dates', function () {
    $stay = Stay::create(['stayID' => 4, ...stayParties(), 'startDate' => '2026-09-01', 'endDate' => '2026-09-05']);

    $stay = Stay::find($stay->id);
    expect($stay->startDate->toDateString())->toBe('2026-09-01');
    expect($stay->endDate->toDateString())->toBe('2026-09-05');
});

it('rejects an end date that is not after the start date', function (string $endDate) {
    Stay::create(['stayID' => 5, ...stayParties(), 'startDate' => '2026-09-05', 'endDate' => $endDate]);
})->with([
    'before the start date' => '2026-09-01',
    'on the start date' => '2026-09-05',
])->throws(QueryException::class);

it('removes the stays when the patient is permanently deleted', function () {
    $parties = stayParties();
    Stay::create(['stayID' => 6, ...$parties, 'startDate' => '2026-09-01']);

    Patient::find($parties['patient_id'])->forceDelete();

    $this->assertDatabaseEmpty('Stay');
});

it('blocks permanently deleting a room that has stays', function () {
    $parties = stayParties();
    Stay::create(['stayID' => 7, ...$parties, 'startDate' => '2026-09-01']);

    Room::find($parties['room_id'])->forceDelete();
})->throws(QueryException::class);

it('keeps the row when a stay is deleted', function () {
    $stay = Stay::create(['stayID' => 8, ...stayParties(), 'startDate' => '2026-09-01']);

    $stay->delete();

    $this->assertSoftDeleted($stay);
});

it('rejects a duplicate stayID', function () {
    $parties = stayParties();
    Stay::create(['stayID' => 1, ...$parties, 'startDate' => '2026-09-01']);

    Stay::create(['stayID' => 1, ...$parties, 'startDate' => '2026-09-01']);
})->throws(UniqueConstraintViolationException::class);

it('assigns the next stayID when none is given', function () {
    $parties = stayParties();
    Stay::create(['stayID' => 1000, ...$parties, 'startDate' => '2026-09-01']);

    $stay = Stay::create([...$parties, 'startDate' => '2026-09-01']);

    expect($stay->stayID)->toBe(1001);
    expect(Stay::find($stay->id)->stayID)->toBe(1001);
});
