<?php

use App\Clinical\Entities\Models\PerformedProcedure;
use App\Clinical\Entities\Models\Stay;
use App\Hospital\Entities\Models\Room;
use App\Patient\Entities\Models\Patient;

beforeEach(function () {
    $this->travelTo('2026-10-15 12:00:00');
});

function procedureDuringStayOf(Patient $patient, string $procDate, ?Room $room = null): PerformedProcedure
{
    $stay = Stay::factory()->for($patient)->for($room ?? Room::factory())->between('2026-10-10')->create();

    return PerformedProcedure::factory()->for($stay)->create(['patient_id' => $patient->id, 'procDate' => $procDate]);
}

it('returns 401 to guests', function () {
    $response = $this->getJson(route('api.patients.upcoming-procedures.index', Patient::factory()->create()));

    $response->assertUnauthorized();
});

it('lists the procedures scheduled for the patient, soonest first', function () {
    $patient = Patient::factory()->create();
    $later = procedureDuringStayOf($patient, '2026-11-02');
    $sooner = procedureDuringStayOf($patient, '2026-10-16');
    procedureDuringStayOf(Patient::factory()->create(), '2026-10-20');

    $response = $this->actingAsLoggedInUser()->getJson(route('api.patients.upcoming-procedures.index', $patient));

    $response->assertOk()->assertJson(['current_page' => 1, 'per_page' => 10, 'total' => 2]);
    expect($response->json('data.*.id'))->toBe([$sooner->id, $later->id]);
});

it('leaves out procedures performed today or earlier', function () {
    $patient = Patient::factory()->create();
    procedureDuringStayOf($patient, '2026-10-15');
    procedureDuringStayOf($patient, '2026-10-12');
    $upcoming = procedureDuringStayOf($patient, '2026-10-16');

    $response = $this->actingAsLoggedInUser()->getJson(route('api.patients.upcoming-procedures.index', $patient));

    expect($response->json('data.*.id'))->toBe([$upcoming->id]);
});

it('returns the requested page of upcoming procedures', function () {
    $patient = Patient::factory()->create();
    collect(range(16, 27))->each(fn (int $day) => procedureDuringStayOf($patient, "2026-10-{$day}"));

    $response = $this->actingAsLoggedInUser()->getJson(route('api.patients.upcoming-procedures.index', [$patient, 'page' => 2, 'per_page' => 5]));

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
    $upcoming = procedureDuringStayOf($patient, '2026-10-20', $room);

    $response = $this->actingAsLoggedInUser()->getJson(route('api.patients.upcoming-procedures.index', $patient));

    expect($response->json('data.0.procDate'))->toBe('2026-10-20');
    expect($response->json('data.0.procedure'))->toMatchArray(['id' => $upcoming->procedure_id, 'name' => $upcoming->procedure->name]);
    expect($response->json('data.0.stay.room'))->toMatchArray(['id' => $room->id, 'roomID' => 214]);
    expect($response->json('data.0.physician'))->toMatchArray(['id' => $upcoming->physician_id, 'name' => $upcoming->physician->name]);
    expect($response->json('data.0.nurse'))->toMatchArray(['id' => $upcoming->nurse_id, 'name' => $upcoming->nurse->name]);
});

it('returns 404 for an inactive patient', function () {
    $patient = Patient::factory()->create();
    $patient->delete();

    $response = $this->actingAsLoggedInUser()->getJson(route('api.patients.upcoming-procedures.index', $patient));

    $response->assertNotFound();
});
