<?php

use App\Staff\Entities\Models\Physician;
use App\Staff\Entities\PhysicianPosition;

it('returns 401 to guests', function () {
    $response = $this->getJson(route('api.prescriber-candidates.index'));

    $response->assertUnauthorized();
});

it('lists every physician except interns, by name', function () {
    Physician::factory()->withPosition(PhysicianPosition::Resident)->create(['name' => 'John Dorian']);
    Physician::factory()->withPosition(PhysicianPosition::Senior)->create(['name' => 'Perry Cox']);
    Physician::factory()->withPosition(PhysicianPosition::Intern)->create(['name' => 'Elliot Reid']);
    Physician::factory()->withPosition(PhysicianPosition::Surgeon)->create(['name' => 'Chris Turk'])->delete();

    $response = $this->actingAsLoggedInUser()->getJson(route('api.prescriber-candidates.index'));

    $response->assertOk();
    expect($response->json('*.name'))->toBe(['John Dorian', 'Perry Cox']);
    expect(array_keys($response->json('0')))->toEqualCanonicalizing(['id', 'physicianID', 'name']);
});
