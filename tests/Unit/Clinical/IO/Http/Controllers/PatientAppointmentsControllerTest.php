<?php

use App\Clinical\Entities\Models\Appointment;
use App\Patient\Entities\Models\Patient;

beforeEach(function () {
    $this->travelTo('2026-10-15 12:00:00');
});

it('returns 401 to guests', function () {
    $response = $this->getJson(route('api.patients.appointments.index', Patient::factory()->create()));

    $response->assertUnauthorized();
});

it('lists the appointments of the patient, latest first', function () {
    $patient = Patient::factory()->create();
    $past = Appointment::factory()->for($patient)->startingAt('2025-03-10 16:00:00')->create();
    $upcoming = Appointment::factory()->for($patient)->startingAt('2026-10-20 15:00:00')->create();
    $recent = Appointment::factory()->for($patient)->startingAt('2026-10-01 15:00:00')->create();
    Appointment::factory()->startingAt('2026-10-21 15:00:00')->create();

    $response = $this->actingAsLoggedInUser()->getJson(route('api.patients.appointments.index', $patient));

    $response->assertOk()->assertJson(['current_page' => 1, 'per_page' => 10, 'total' => 3]);
    expect($response->json('data.*.id'))->toBe([$upcoming->id, $recent->id, $past->id]);
});

it('leaves out cancelled appointments', function () {
    $patient = Patient::factory()->create();
    $kept = Appointment::factory()->for($patient)->startingAt('2026-10-20 15:00:00')->create();
    Appointment::factory()->for($patient)->startingAt('2026-10-21 15:00:00')->create()->delete();

    $response = $this->actingAsLoggedInUser()->getJson(route('api.patients.appointments.index', $patient));

    expect($response->json('data.*.id'))->toBe([$kept->id]);
});

it('returns the requested page of appointments', function () {
    $patient = Patient::factory()->create();
    collect(range(1, 12))->each(fn (int $day) => Appointment::factory()->for($patient)->startingAt(sprintf('2026-09-%02d 15:00:00', $day))->create());

    $response = $this->actingAsLoggedInUser()->getJson(route('api.patients.appointments.index', [$patient, 'page' => 2, 'per_page' => 5]));

    $response->assertOk()->assertJsonCount(5, 'data')->assertJson([
        'current_page' => 2,
        'per_page' => 5,
        'total' => 12,
        'last_page' => 3,
    ]);
});

it('includes the physician and nurse of each appointment', function () {
    $patient = Patient::factory()->create();
    $appointment = Appointment::factory()->for($patient)->create();

    $response = $this->actingAsLoggedInUser()->getJson(route('api.patients.appointments.index', $patient));

    expect($response->json('data.0.physician'))->toMatchArray(['id' => $appointment->physician_id, 'name' => $appointment->physician->name]);
    expect($response->json('data.0.nurse'))->toMatchArray(['id' => $appointment->nurse_id, 'name' => $appointment->nurse->name]);
});

it('returns 404 for an inactive patient', function () {
    $patient = Patient::factory()->create();
    $patient->delete();

    $response = $this->actingAsLoggedInUser()->getJson(route('api.patients.appointments.index', $patient));

    $response->assertNotFound();
});
