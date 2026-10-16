<?php

use App\Clinical\Entities\Models\PerformedProcedure;
use App\Clinical\Entities\Models\Stay;
use App\Hospital\Entities\Models\Room;
use App\Patient\Entities\Models\Patient;
use Carbon\CarbonImmutable;

beforeEach(function () {
    $this->travelTo('2026-10-15 12:00:00');
});

function performedProcedureOf(Patient $patient, string $procDate, ?Room $room = null): PerformedProcedure
{
    $stay = Stay::factory()->for($patient)->for($room ?? Room::factory())->between($procDate, CarbonImmutable::parse($procDate)->addDay()->toDateString())->create();

    return PerformedProcedure::factory()->for($stay)->create(['patient_id' => $patient->id, 'procDate' => $procDate]);
}

it('returns 401 to guests', function () {
    $response = $this->getJson(route('api.patients.performed-procedures.index', Patient::factory()->create()));

    $response->assertUnauthorized();
});

it('lists the procedures performed on the patient, most recent first', function () {
    $patient = Patient::factory()->create();
    $older = performedProcedureOf($patient, '2025-03-10');
    $newer = performedProcedureOf($patient, '2026-10-15');
    performedProcedureOf(Patient::factory()->create(), '2026-10-01');

    $response = $this->actingAsLoggedInUser()->getJson(route('api.patients.performed-procedures.index', $patient));

    $response->assertOk()->assertJson(['current_page' => 1, 'per_page' => 10, 'total' => 2]);
    expect($response->json('data.*.id'))->toBe([$newer->id, $older->id]);
});

it('leaves out procedures scheduled for later', function () {
    $patient = Patient::factory()->create();
    $performed = performedProcedureOf($patient, '2026-10-14');
    performedProcedureOf($patient, '2026-10-16');

    $response = $this->actingAsLoggedInUser()->getJson(route('api.patients.performed-procedures.index', $patient));

    expect($response->json('data.*.id'))->toBe([$performed->id]);
});

it('returns the requested page of performed procedures', function () {
    $patient = Patient::factory()->create();
    collect(range(1, 12))->each(fn (int $day) => performedProcedureOf($patient, sprintf('2026-09-%02d', $day)));

    $response = $this->actingAsLoggedInUser()->getJson(route('api.patients.performed-procedures.index', [$patient, 'page' => 2, 'per_page' => 5]));

    $response->assertOk()->assertJsonCount(5, 'data')->assertJson([
        'current_page' => 2,
        'per_page' => 5,
        'total' => 12,
        'last_page' => 3,
    ]);
});

it('includes the procedure, room, physician and nurse', function () {
    $patient = Patient::factory()->create();
    $room = Room::factory()->create(['roomID' => 214]);
    $performedProcedure = performedProcedureOf($patient, '2026-10-07', $room);

    $response = $this->actingAsLoggedInUser()->getJson(route('api.patients.performed-procedures.index', $patient));

    expect($response->json('data.0.procDate'))->toBe('2026-10-07');
    expect($response->json('data.0.procedure'))->toMatchArray(['id' => $performedProcedure->procedure_id, 'name' => $performedProcedure->procedure->name]);
    expect($response->json('data.0.stay.room'))->toMatchArray(['id' => $room->id, 'roomID' => 214]);
    expect($response->json('data.0.physician'))->toMatchArray(['id' => $performedProcedure->physician_id, 'name' => $performedProcedure->physician->name]);
    expect($response->json('data.0.nurse'))->toMatchArray(['id' => $performedProcedure->nurse_id, 'name' => $performedProcedure->nurse->name]);
});

it('returns 404 for an inactive patient', function () {
    $patient = Patient::factory()->create();
    $patient->delete();

    $response = $this->actingAsLoggedInUser()->getJson(route('api.patients.performed-procedures.index', $patient));

    $response->assertNotFound();
});
