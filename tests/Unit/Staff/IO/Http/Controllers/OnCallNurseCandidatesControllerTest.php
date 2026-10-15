<?php

use App\Staff\Entities\Models\Nurse;

it('returns 401 to guests', function () {
    $response = $this->getJson(route('api.on-call-nurse-candidates.index'));

    $response->assertUnauthorized();
});

it('lists the nurses on staff by name', function () {
    Nurse::factory()->create(['name' => 'Laverne Roberts']);
    Nurse::factory()->create(['name' => 'Carla Espinosa']);
    Nurse::factory()->create(['name' => 'Shirley Hart'])->delete();

    $response = $this->actingAsLoggedInUser()->getJson(route('api.on-call-nurse-candidates.index'));

    $response->assertOk();
    expect($response->json('*.name'))->toBe(['Carla Espinosa', 'Laverne Roberts']);
    expect(array_keys($response->json('0')))->toEqualCanonicalizing(['id', 'nurseID', 'name', 'position']);
});
