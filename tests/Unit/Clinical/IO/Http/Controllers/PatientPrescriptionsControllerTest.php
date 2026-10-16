<?php

use App\Clinical\Entities\Models\Prescription;
use App\Patient\Entities\Models\Patient;

it('returns 401 to guests', function () {
    $response = $this->getJson(route('api.patients.prescriptions.index', Patient::factory()->create()));

    $response->assertUnauthorized();
});

it('lists the prescriptions of the patient, most recently prescribed first', function () {
    $patient = Patient::factory()->create();
    $older = Prescription::factory()->for($patient)->prescribedOn('2024-02-01')->create();
    $newer = Prescription::factory()->for($patient)->prescribedOn('2026-09-30')->create();
    Prescription::factory()->prescribedOn('2026-10-01')->create();

    $response = $this->actingAsLoggedInUser()->getJson(route('api.patients.prescriptions.index', $patient));

    $response->assertOk()->assertJson(['current_page' => 1, 'per_page' => 10, 'total' => 2]);
    expect($response->json('data.*.id'))->toBe([$newer->id, $older->id]);
});

it('returns the requested page of prescriptions', function () {
    $patient = Patient::factory()->create();
    Prescription::factory()->for($patient)->count(12)->create();

    $response = $this->actingAsLoggedInUser()->getJson(route('api.patients.prescriptions.index', [$patient, 'page' => 2, 'per_page' => 5]));

    $response->assertOk()->assertJsonCount(5, 'data')->assertJson([
        'current_page' => 2,
        'per_page' => 5,
        'total' => 12,
        'last_page' => 3,
    ]);
});

it('includes the medication, dose and prescribing physician', function () {
    $patient = Patient::factory()->create();
    $prescription = Prescription::factory()->for($patient)->prescribedOn('2026-09-30')->create(['dose' => '500 mg twice daily']);
    $prescription->physician->delete();

    $response = $this->actingAsLoggedInUser()->getJson(route('api.patients.prescriptions.index', $patient));

    expect($response->json('data.0'))->toMatchArray(['prescribedDate' => '2026-09-30', 'dose' => '500 mg twice daily']);
    expect($response->json('data.0.medication'))->toMatchArray(['id' => $prescription->medication_id, 'name' => $prescription->medication->name]);
    expect($response->json('data.0.physician.name'))->toBe($prescription->physician->name);
    expect($response->json('data.0.physician.deleted_at'))->not->toBeNull();
});

it('returns 404 for an inactive patient', function () {
    $patient = Patient::factory()->create();
    $patient->delete();

    $response = $this->actingAsLoggedInUser()->getJson(route('api.patients.prescriptions.index', $patient));

    $response->assertNotFound();
});
