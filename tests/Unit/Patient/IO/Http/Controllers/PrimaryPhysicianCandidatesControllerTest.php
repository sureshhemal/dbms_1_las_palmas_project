<?php

use App\Staff\Entities\Models\Physician;
use App\Staff\Entities\PhysicianPosition;

it('returns 401 to guests', function () {
    $response = $this->getJson(route('api.primary-physician-candidates.index'));

    $response->assertUnauthorized();
});

it('lists every physician except interns, by name', function () {
    Physician::factory()->withPosition(PhysicianPosition::Surgeon)->create(['name' => 'Chris Turk']);
    Physician::factory()->withPosition(PhysicianPosition::Resident)->create(['name' => 'John Dorian']);
    Physician::factory()->withPosition(PhysicianPosition::ChiefOfMedicine)->create(['name' => 'Bob Kelso']);
    Physician::factory()->withPosition(PhysicianPosition::Intern)->create(['name' => 'Elliot Reid']);

    $response = $this->actingAsLoggedInUser()->getJson(route('api.primary-physician-candidates.index'));

    $response->assertOk();
    expect($response->json('*.name'))->toBe(['Bob Kelso', 'Chris Turk', 'John Dorian']);
    expect(array_keys($response->json('0')))->toEqualCanonicalizing(['id', 'physicianID', 'name']);
});

it('leaves out offboarded physicians', function () {
    Physician::factory()->withPosition(PhysicianPosition::Senior)->create()->delete();

    $response = $this->actingAsLoggedInUser()->getJson(route('api.primary-physician-candidates.index'));

    $response->assertOk()->assertJsonCount(0);
});
