<?php

use App\Staff\Entities\Models\Physician;
use App\Staff\Entities\PhysicianPosition;

it('returns 401 to guests', function () {
    $response = $this->getJson(route('api.department-head-candidates.index'));

    $response->assertUnauthorized();
});

it('lists only the physicians who can head a department, by name', function () {
    Physician::factory()->withPosition(PhysicianPosition::Surgeon)->create(['name' => 'Chris Turk']);
    Physician::factory()->withPosition(PhysicianPosition::ChiefOfMedicine)->create(['name' => 'Bob Kelso']);
    Physician::factory()->withPosition(PhysicianPosition::Intern)->create(['name' => 'Elliot Reid']);
    Physician::factory()->withPosition(PhysicianPosition::Resident)->create(['name' => 'John Dorian']);

    $response = $this->actingAsLoggedInUser()->getJson(route('api.department-head-candidates.index'));

    $response->assertOk();
    expect($response->json('*.name'))->toBe(['Bob Kelso', 'Chris Turk']);
});

it('leaves out offboarded physicians', function () {
    Physician::factory()->withPosition(PhysicianPosition::Senior)->create()->delete();

    $response = $this->actingAsLoggedInUser()->getJson(route('api.department-head-candidates.index'));

    $response->assertOk()->assertJsonCount(0);
});
