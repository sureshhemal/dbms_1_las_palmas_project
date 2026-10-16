<?php

use App\Clinical\Entities\Models\PerformedProcedure;
use App\Hospital\Entities\Models\Room;
use App\Staff\Entities\Models\Physician;

beforeEach(function () {
    $this->travelTo('2026-10-15 12:00:00');
});

it('returns 401 to guests', function () {
    $response = $this->getJson(route('api.physicians.performed-procedures.index', Physician::factory()->create()));

    $response->assertUnauthorized();
});

it('lists the procedures the physician performed, most recent first', function () {
    $physician = Physician::factory()->create();
    $older = PerformedProcedure::factory()->for($physician)->performedOn('2025-03-10')->create();
    $newer = PerformedProcedure::factory()->for($physician)->performedOn('2026-10-15')->create();
    PerformedProcedure::factory()->performedOn('2026-10-15')->create();

    $response = $this->actingAsLoggedInUser()->getJson(route('api.physicians.performed-procedures.index', $physician));

    $response->assertOk()->assertJson(['current_page' => 1, 'per_page' => 10, 'total' => 2]);
    expect($response->json('data.*.id'))->toBe([$newer->id, $older->id]);
});

it('leaves out procedures scheduled for later', function () {
    $physician = Physician::factory()->create();
    $listed = PerformedProcedure::factory()->for($physician)->performedOn('2026-10-14')->create();
    PerformedProcedure::factory()->for($physician)->performedOn('2026-10-16')->create();

    $response = $this->actingAsLoggedInUser()->getJson(route('api.physicians.performed-procedures.index', $physician));

    expect($response->json('data.*.id'))->toBe([$listed->id]);
});

it('returns the requested page', function () {
    $physician = Physician::factory()->create();
    collect(range(1, 12))->each(fn (int $day) => PerformedProcedure::factory()->for($physician)->performedOn(sprintf('2026-09-%02d', $day))->create());

    $response = $this->actingAsLoggedInUser()->getJson(route('api.physicians.performed-procedures.index', [$physician, 'page' => 2, 'per_page' => 5]));

    $response->assertOk()->assertJsonCount(5, 'data')->assertJson([
        'current_page' => 2,
        'per_page' => 5,
        'total' => 12,
        'last_page' => 3,
    ]);
});

it('includes the patient, procedure, room and nurse', function () {
    $physician = Physician::factory()->create();
    $procedure = PerformedProcedure::factory()->for($physician)->performedOn('2026-10-07')->create();
    $procedure->stay->room()->associate(Room::factory()->create(['roomID' => 214]))->save();

    $response = $this->actingAsLoggedInUser()->getJson(route('api.physicians.performed-procedures.index', $physician));

    expect($response->json('data.0.procDate'))->toBe('2026-10-07');
    expect($response->json('data.0.patient'))->toMatchArray(['id' => $procedure->patient_id, 'name' => $procedure->patient->name]);
    expect($response->json('data.0.procedure'))->toMatchArray(['id' => $procedure->procedure_id, 'name' => $procedure->procedure->name]);
    expect($response->json('data.0.stay.room.roomID'))->toBe(214);
    expect($response->json('data.0.nurse'))->toMatchArray(['id' => $procedure->nurse_id, 'name' => $procedure->nurse->name]);
});

it('returns 404 for an offboarded physician', function () {
    $physician = Physician::factory()->create();
    $physician->delete();

    $response = $this->actingAsLoggedInUser()->getJson(route('api.physicians.performed-procedures.index', $physician));

    $response->assertNotFound();
});
