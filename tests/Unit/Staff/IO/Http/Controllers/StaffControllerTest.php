<?php

use App\Staff\Entities\Models\Nurse;
use App\Staff\Entities\Models\OnCall;
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
        'on_call_nurse_count' => 0,
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
        'on_call_nurse_count' => 0,
    ]);
});

it('counts the nurses on call on the hospital day, not the server day', function () {
    config(['app.hospital_timezone' => 'America/Denver']);
    $this->travelTo('2026-10-16 03:00:00');
    OnCall::factory()->between('2026-10-14', '2026-10-16')->create();
    OnCall::factory()->between('2026-10-16', '2026-10-18')->create();

    $response = $this->actingAsLoggedInUser()->getJson(route('api.staff.index'));

    expect($response->json('on_call_nurse_count'))->toBe(1);
});

it('counts the nurses who are on call today', function () {
    $this->travelTo('2026-10-15');
    OnCall::factory()->between('2026-10-14', '2026-10-16')->create();
    OnCall::factory()->between('2026-10-15', '2026-10-20')->create();
    OnCall::factory()->between('2026-10-10', '2026-10-15')->create();
    OnCall::factory()->between('2026-10-10', '2026-10-14')->create();
    OnCall::factory()->between('2026-10-16', '2026-10-18')->create();
    OnCall::factory()->between('2026-10-14', '2026-10-16')->create()->nurse->delete();

    $response = $this->actingAsLoggedInUser()->getJson(route('api.staff.index'));

    expect($response->json('on_call_nurse_count'))->toBe(3);
});
