<?php

use App\Staff\Entities\Models\Nurse;
use App\Staff\Entities\Models\OnCall;
use App\Staff\Entities\NursePosition;
use Illuminate\Database\QueryException;

function onCallNurse(): Nurse
{
    return Nurse::create(['nurseID' => 1, 'name' => 'Carla Espinosa', 'position' => NursePosition::Nurse]);
}

it('returns the nurse', function () {
    $nurse = onCallNurse();

    $onCall = OnCall::create(['nurse_id' => $nurse->id, 'startDate' => '2026-09-01', 'endDate' => '2026-09-03']);

    expect($onCall->nurse->is($nurse))->toBeTrue();
});

it('generates a ulid primary key', function () {
    $onCall = OnCall::create(['nurse_id' => onCallNurse()->id, 'startDate' => '2026-09-01', 'endDate' => '2026-09-03']);

    expect($onCall->getKey())->toBeString()->toHaveLength(26);
});

it('reads the dates back as dates', function () {
    $onCall = OnCall::create(['nurse_id' => onCallNurse()->id, 'startDate' => '2026-09-01', 'endDate' => '2026-09-03']);

    $onCall = OnCall::find($onCall->id);
    expect($onCall->startDate->toDateString())->toBe('2026-09-01');
    expect($onCall->endDate->toDateString())->toBe('2026-09-03');
});

it('rejects an end date that is not after the start date', function (string $endDate) {
    OnCall::create(['nurse_id' => onCallNurse()->id, 'startDate' => '2026-09-05', 'endDate' => $endDate]);
})->with([
    'before the start date' => '2026-09-01',
    'on the start date' => '2026-09-05',
])->throws(QueryException::class);

it('rejects the same on-call period for a nurse twice', function () {
    $nurse = onCallNurse();
    OnCall::create(['nurse_id' => $nurse->id, 'startDate' => '2026-09-01', 'endDate' => '2026-09-03']);

    OnCall::create(['nurse_id' => $nurse->id, 'startDate' => '2026-09-01', 'endDate' => '2026-09-03']);
})->throws(QueryException::class);

it('removes the on-call periods when the nurse is permanently deleted', function () {
    $nurse = onCallNurse();
    OnCall::create(['nurse_id' => $nurse->id, 'startDate' => '2026-09-01', 'endDate' => '2026-09-03']);

    $nurse->forceDelete();

    $this->assertDatabaseEmpty('OnCall');
});

it('removes the row when an on-call period is deleted', function () {
    $onCall = OnCall::create(['nurse_id' => onCallNurse()->id, 'startDate' => '2026-09-01', 'endDate' => '2026-09-03']);

    $onCall->delete();

    $this->assertModelMissing($onCall);
});

it('serializes the dates without a time', function () {
    $onCall = OnCall::create(['nurse_id' => onCallNurse()->id, 'startDate' => '2026-09-01', 'endDate' => '2026-09-03']);

    expect($onCall->fresh()->toArray())->toMatchArray(['startDate' => '2026-09-01', 'endDate' => '2026-09-03']);
});
