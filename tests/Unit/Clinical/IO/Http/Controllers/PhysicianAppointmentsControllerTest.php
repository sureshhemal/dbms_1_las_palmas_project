<?php

use App\Clinical\Entities\Models\Appointment;
use App\Staff\Entities\Models\Physician;

beforeEach(function () {
    $this->travelTo('2026-10-15 12:00:00');
});

it('returns 401 to guests', function () {
    $response = $this->getJson(route('api.physicians.appointments.index', Physician::factory()->create()));

    $response->assertUnauthorized();
});

it('lists the appointments of the physician, latest first', function () {
    $physician = Physician::factory()->create();
    $past = Appointment::factory()->for($physician)->startingAt('2025-03-10 16:00:00')->create();
    $upcoming = Appointment::factory()->for($physician)->startingAt('2026-10-20 15:00:00')->create();
    $recent = Appointment::factory()->for($physician)->startingAt('2026-10-01 15:00:00')->create();
    Appointment::factory()->startingAt('2026-10-21 15:00:00')->create();

    $response = $this->actingAsLoggedInUser()->getJson(route('api.physicians.appointments.index', $physician));

    $response->assertOk()->assertJson(['current_page' => 1, 'per_page' => 10, 'total' => 3]);
    expect($response->json('data.*.id'))->toBe([$upcoming->id, $recent->id, $past->id]);
});

it('leaves out cancelled appointments', function () {
    $physician = Physician::factory()->create();
    $kept = Appointment::factory()->for($physician)->startingAt('2026-10-20 15:00:00')->create();
    Appointment::factory()->for($physician)->startingAt('2026-10-21 15:00:00')->create()->delete();

    $response = $this->actingAsLoggedInUser()->getJson(route('api.physicians.appointments.index', $physician));

    expect($response->json('data.*.id'))->toBe([$kept->id]);
});

it('returns the requested page of appointments', function () {
    $physician = Physician::factory()->create();
    collect(range(1, 12))->each(fn (int $day) => Appointment::factory()->for($physician)->startingAt(sprintf('2026-09-%02d 15:00:00', $day))->create());

    $response = $this->actingAsLoggedInUser()->getJson(route('api.physicians.appointments.index', [$physician, 'page' => 2, 'per_page' => 5]));

    $response->assertOk()->assertJsonCount(5, 'data')->assertJson([
        'current_page' => 2,
        'per_page' => 5,
        'total' => 12,
        'last_page' => 3,
    ]);
});

it('includes the patient and nurse of each appointment', function () {
    $physician = Physician::factory()->create();
    $appointment = Appointment::factory()->for($physician)->create();

    $response = $this->actingAsLoggedInUser()->getJson(route('api.physicians.appointments.index', $physician));

    expect($response->json('data.0.patient'))->toMatchArray(['id' => $appointment->patient_id, 'name' => $appointment->patient->name]);
    expect($response->json('data.0.nurse'))->toMatchArray(['id' => $appointment->nurse_id, 'name' => $appointment->nurse->name]);
});

it('returns 404 for an inactive physician', function () {
    $physician = Physician::factory()->create();
    $physician->delete();

    $response = $this->actingAsLoggedInUser()->getJson(route('api.physicians.appointments.index', $physician));

    $response->assertNotFound();
});
