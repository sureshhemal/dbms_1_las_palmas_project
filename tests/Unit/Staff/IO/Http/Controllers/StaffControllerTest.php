<?php

use App\Staff\Entities\Models\Nurse;
use App\Staff\Entities\Models\Physician;

it('returns 401 to guests', function () {
    $response = $this->getJson(route('api.staff.index'));

    $response->assertUnauthorized();
});

it('returns the number of physicians and nurses', function () {
    Physician::factory()->count(3)->create();
    Nurse::factory()->count(2)->create();

    $response = $this->actingAsLoggedInUser()->getJson(route('api.staff.index'));

    $response->assertOk()->assertExactJson([
        'physician_count' => 3,
        'nurse_count' => 2,
    ]);
});

it('leaves soft-deleted physicians and nurses out of the counts', function () {
    Physician::factory()->create();
    Physician::factory()->create()->delete();
    Nurse::factory()->create()->delete();

    $response = $this->actingAsLoggedInUser()->getJson(route('api.staff.index'));

    $response->assertExactJson([
        'physician_count' => 1,
        'nurse_count' => 0,
    ]);
});
