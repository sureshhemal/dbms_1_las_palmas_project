<?php

use App\Staff\Entities\Models\Nurse;
use App\Staff\Entities\Models\OnCall;

it('returns 401 to guests', function () {
    $response = $this->getJson(route('api.on-calls.index'));

    $response->assertUnauthorized();
});

it('lists the on-call periods with their nurse, earliest first', function () {
    $nurse = Nurse::factory()->create(['name' => 'Carla Espinosa']);
    $laterOnCall = OnCall::factory()->for($nurse)->between('2026-10-10', '2026-10-12')->create();
    $earlierOnCall = OnCall::factory()->for($nurse)->between('2026-10-01', '2026-10-03')->create();

    $response = $this->actingAsLoggedInUser()->getJson(route('api.on-calls.index'));

    $response->assertOk()->assertJsonCount(2);
    expect($response->json('*.id'))->toBe([$earlierOnCall->id, $laterOnCall->id]);
    expect($response->json('0'))->toMatchArray([
        'startDate' => '2026-10-01',
        'endDate' => '2026-10-03',
        'nurse' => ['id' => $nurse->id, 'nurseID' => $nurse->nurseID, 'name' => 'Carla Espinosa', 'position' => $nurse->position->value],
    ]);
});

it('lists only the on-call periods that overlap the requested dates', function () {
    $endingOnFirstDay = OnCall::factory()->between('2026-09-28', '2026-10-01')->create();
    $startingOnLastDay = OnCall::factory()->between('2026-10-31', '2026-11-03')->create();
    OnCall::factory()->between('2026-09-25', '2026-09-30')->create();
    OnCall::factory()->between('2026-11-01', '2026-11-05')->create();

    $response = $this->actingAsLoggedInUser()->getJson(route('api.on-calls.index', ['from' => '2026-10-01', 'to' => '2026-10-31']));

    expect($response->json('*.id'))->toBe([$endingOnFirstDay->id, $startingOnLastDay->id]);
});

it('filters the on-call periods by nurse', function () {
    $chosenNurseOnCall = OnCall::factory()->create();
    OnCall::factory()->create();

    $response = $this->actingAsLoggedInUser()->getJson(route('api.on-calls.index', ['nurse_ids' => [$chosenNurseOnCall->nurse_id]]));

    expect($response->json('*.id'))->toBe([$chosenNurseOnCall->id]);
});

it('leaves out the on-call periods of offboarded nurses', function () {
    $keptOnCall = OnCall::factory()->create();
    OnCall::factory()->create()->nurse->delete();

    $response = $this->actingAsLoggedInUser()->getJson(route('api.on-calls.index'));

    expect($response->json('*.id'))->toBe([$keptOnCall->id]);
});

it('returns 401 to guests adding an on-call period', function () {
    $response = $this->postJson(route('api.on-calls.store'), [
        'nurse_id' => Nurse::factory()->create()->id,
        'startDate' => '2026-10-01',
        'endDate' => '2026-10-03',
    ]);

    $response->assertUnauthorized();
    $this->assertDatabaseEmpty('OnCall');
});

it('adds an on-call period', function () {
    $nurse = Nurse::factory()->create(['name' => 'Carla Espinosa']);

    $response = $this->actingAsLoggedInUser()->postJson(route('api.on-calls.store'), [
        'nurse_id' => $nurse->id,
        'startDate' => '2026-10-01',
        'endDate' => '2026-10-03',
    ]);

    $response->assertCreated()->assertJson([
        'startDate' => '2026-10-01',
        'endDate' => '2026-10-03',
        'nurse' => ['name' => 'Carla Espinosa'],
    ]);
    $this->assertDatabaseHas('OnCall', ['nurse_id' => $nurse->id, 'startDate' => '2026-10-01', 'endDate' => '2026-10-03']);
});

it('stores the dates exactly as chosen, whatever the hospital timezone', function () {
    config(['app.hospital_timezone' => 'Pacific/Kiritimati']);
    $nurse = Nurse::factory()->create();

    $this->actingAsLoggedInUser()->postJson(route('api.on-calls.store'), [
        'nurse_id' => $nurse->id,
        'startDate' => '2026-10-01',
        'endDate' => '2026-10-03',
    ])->assertCreated()->assertJson(['startDate' => '2026-10-01', 'endDate' => '2026-10-03']);

    $this->assertDatabaseHas('OnCall', ['nurse_id' => $nurse->id, 'startDate' => '2026-10-01', 'endDate' => '2026-10-03']);
});

it('lets the next on-call period start the day after the last day', function () {
    $onCall = OnCall::factory()->between('2026-10-01', '2026-10-03')->create();

    $response = $this->actingAsLoggedInUser()->postJson(route('api.on-calls.store'), [
        'nurse_id' => $onCall->nurse_id,
        'startDate' => '2026-10-04',
        'endDate' => '2026-10-05',
    ]);

    $response->assertCreated();
});

it('rejects an on-call period that overlaps another one of the same nurse', function (string $startDate, string $endDate) {
    $nurse = Nurse::factory()->create(['name' => 'Carla Espinosa']);
    OnCall::factory()->for($nurse)->between('2026-10-05', '2026-10-10')->create();

    $response = $this->actingAsLoggedInUser()->postJson(route('api.on-calls.store'), [
        'nurse_id' => $nurse->id,
        'startDate' => $startDate,
        'endDate' => $endDate,
    ]);

    $response->assertUnprocessable()->assertJsonValidationErrors([
        'startDate' => 'Carla Espinosa is already on call from 2026-10-05 to 2026-10-10.',
    ]);
    expect(OnCall::count())->toBe(1);
})->with([
    'starting before and ending inside' => ['2026-10-03', '2026-10-06'],
    'inside' => ['2026-10-06', '2026-10-08'],
    'starting inside and ending after' => ['2026-10-09', '2026-10-12'],
    'covering it' => ['2026-10-01', '2026-10-15'],
    'starting on its last day' => ['2026-10-10', '2026-10-12'],
    'ending on its first day' => ['2026-10-03', '2026-10-05'],
]);

it('allows overlapping on-call periods of different nurses', function () {
    OnCall::factory()->between('2026-10-05', '2026-10-10')->create();

    $response = $this->actingAsLoggedInUser()->postJson(route('api.on-calls.store'), [
        'nurse_id' => Nurse::factory()->create()->id,
        'startDate' => '2026-10-05',
        'endDate' => '2026-10-10',
    ]);

    $response->assertCreated();
});

it('shows the MySQL error when the end date is not after the start date', function () {
    config(['app.debug' => true]);

    $response = $this->actingAsLoggedInUser()->postJson(route('api.on-calls.store'), [
        'nurse_id' => Nurse::factory()->create()->id,
        'startDate' => '2026-10-05',
        'endDate' => '2026-10-05',
    ]);

    $response->assertInternalServerError();
    expect($response->json('message'))->toContain('oncall_dates_check');
});

it('shows the MySQL error when the nurse is left blank', function () {
    config(['app.debug' => true]);

    $response = $this->actingAsLoggedInUser()->postJson(route('api.on-calls.store'), [
        'nurse_id' => '',
        'startDate' => '2026-10-01',
        'endDate' => '2026-10-03',
    ]);

    $response->assertInternalServerError();
    expect($response->json('message'))->toContain("Column 'nurse_id' cannot be null");
});

it('returns 401 to guests updating an on-call period', function () {
    $onCall = OnCall::factory()->between('2026-10-01', '2026-10-03')->create();

    $response = $this->putJson(route('api.on-calls.update', $onCall), [
        'nurse_id' => $onCall->nurse_id,
        'startDate' => '2026-10-02',
        'endDate' => '2026-10-04',
    ]);

    $response->assertUnauthorized();
    expect($onCall->fresh()->startDate->toDateString())->toBe('2026-10-01');
});

it('moves an on-call period to other dates and another nurse', function () {
    $onCall = OnCall::factory()->between('2026-10-01', '2026-10-03')->create();
    $otherNurse = Nurse::factory()->create();

    $response = $this->actingAsLoggedInUser()->putJson(route('api.on-calls.update', $onCall), [
        'nurse_id' => $otherNurse->id,
        'startDate' => '2026-10-02',
        'endDate' => '2026-10-06',
    ]);

    $response->assertOk()->assertJson(['startDate' => '2026-10-02', 'endDate' => '2026-10-06', 'nurse' => ['id' => $otherNurse->id]]);
    $this->assertDatabaseHas('OnCall', ['id' => $onCall->id, 'nurse_id' => $otherNurse->id, 'startDate' => '2026-10-02', 'endDate' => '2026-10-06']);
});

it('does not count the on-call period being updated as an overlap', function () {
    $onCall = OnCall::factory()->between('2026-10-01', '2026-10-05')->create();

    $response = $this->actingAsLoggedInUser()->putJson(route('api.on-calls.update', $onCall), [
        'nurse_id' => $onCall->nurse_id,
        'startDate' => '2026-10-01',
        'endDate' => '2026-10-07',
    ]);

    $response->assertOk();
});

it('rejects an update that overlaps another on-call period of the same nurse', function () {
    $nurse = Nurse::factory()->create();
    OnCall::factory()->for($nurse)->between('2026-10-05', '2026-10-10')->create();
    $onCall = OnCall::factory()->for($nurse)->between('2026-10-01', '2026-10-03')->create();

    $response = $this->actingAsLoggedInUser()->putJson(route('api.on-calls.update', $onCall), [
        'nurse_id' => $nurse->id,
        'startDate' => '2026-10-01',
        'endDate' => '2026-10-06',
    ]);

    $response->assertUnprocessable()->assertJsonValidationErrors('startDate');
    expect($onCall->fresh()->endDate->toDateString())->toBe('2026-10-03');
});

it('returns 401 to guests removing an on-call period', function () {
    $onCall = OnCall::factory()->create();

    $response = $this->deleteJson(route('api.on-calls.destroy', $onCall));

    $response->assertUnauthorized();
    $this->assertModelExists($onCall);
});

it('removes an on-call period', function () {
    $onCall = OnCall::factory()->create();

    $response = $this->actingAsLoggedInUser()->deleteJson(route('api.on-calls.destroy', $onCall));

    $response->assertNoContent();
    $this->assertModelMissing($onCall);
});
