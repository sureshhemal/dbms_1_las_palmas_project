<?php

use App\Clinical\Entities\Models\Prescription;
use App\Staff\Entities\Models\Physician;

it('returns 401 to guests', function () {
    $response = $this->getJson(route('api.physicians.prescriptions.index', Physician::factory()->create()));

    $response->assertUnauthorized();
});

it('lists the prescriptions the physician issued, most recently prescribed first', function () {
    $physician = Physician::factory()->create();
    $older = Prescription::factory()->for($physician)->prescribedOn('2024-02-01')->create();
    $newer = Prescription::factory()->for($physician)->prescribedOn('2026-09-30')->create();
    Prescription::factory()->prescribedOn('2026-10-01')->create();

    $response = $this->actingAsLoggedInUser()->getJson(route('api.physicians.prescriptions.index', $physician));

    $response->assertOk()->assertJson(['current_page' => 1, 'per_page' => 10, 'total' => 2]);
    expect($response->json('data.*.id'))->toBe([$newer->id, $older->id]);
});

it('returns the requested page of prescriptions', function () {
    $physician = Physician::factory()->create();
    Prescription::factory()->for($physician)->count(12)->create();

    $response = $this->actingAsLoggedInUser()->getJson(route('api.physicians.prescriptions.index', [$physician, 'page' => 2, 'per_page' => 5]));

    $response->assertOk()->assertJsonCount(5, 'data')->assertJson([
        'current_page' => 2,
        'per_page' => 5,
        'total' => 12,
        'last_page' => 3,
    ]);
});

it('includes the patient, medication and dose', function () {
    $physician = Physician::factory()->create();
    $prescription = Prescription::factory()->for($physician)->prescribedOn('2026-09-30')->create(['dose' => '500 mg twice daily']);
    $prescription->patient->delete();
    $prescription->medication->delete();

    $response = $this->actingAsLoggedInUser()->getJson(route('api.physicians.prescriptions.index', $physician));

    expect($response->json('data.0'))->toMatchArray(['prescribedDate' => '2026-09-30', 'dose' => '500 mg twice daily']);
    expect($response->json('data.0.patient'))->toMatchArray(['id' => $prescription->patient_id, 'name' => $prescription->patient->name]);
    expect($response->json('data.0.patient.deleted_at'))->not->toBeNull();
    expect($response->json('data.0.medication'))->toMatchArray(['id' => $prescription->medication_id, 'name' => $prescription->medication->name]);
    expect($response->json('data.0.medication.deleted_at'))->not->toBeNull();
});

it('returns 404 for an offboarded physician', function () {
    $physician = Physician::factory()->create();
    $physician->delete();

    $response = $this->actingAsLoggedInUser()->getJson(route('api.physicians.prescriptions.index', $physician));

    $response->assertNotFound();
});
