<?php

use App\Clinical\Entities\Models\Procedure;

it('returns 401 to guests', function () {
    $response = $this->getJson(route('api.procedures.average-cost.show'));

    $response->assertUnauthorized();
});

it('averages the cost of the active procedures, leaving out those without a cost', function () {
    Procedure::factory()->create(['cost' => 100]);
    Procedure::factory()->create(['cost' => 200]);
    Procedure::factory()->create(['cost' => 600]);
    Procedure::factory()->create(['cost' => null]);
    Procedure::factory()->create(['cost' => 10000])->delete();

    $response = $this->actingAsLoggedInUser()->getJson(route('api.procedures.average-cost.show'));

    $response->assertOk();
    expect($response->json('averageCost'))->toEqual(300);
    expect($response->json('pricedProcedureCount'))->toBe(3);
    expect($response->json('unpricedProcedureCount'))->toBe(1);
});

it('keeps the cents of the average cost', function () {
    Procedure::factory()->create(['cost' => 100.10]);
    Procedure::factory()->create(['cost' => 100.25]);

    $response = $this->actingAsLoggedInUser()->getJson(route('api.procedures.average-cost.show'));

    expect($response->json('averageCost'))->toEqual(100.175);
});

it('has no average cost when no procedure has a cost yet', function () {
    Procedure::factory()->create(['cost' => null]);

    $response = $this->actingAsLoggedInUser()->getJson(route('api.procedures.average-cost.show'));

    $response->assertOk()->assertExactJson([
        'averageCost' => null,
        'pricedProcedureCount' => 0,
        'unpricedProcedureCount' => 1,
    ]);
});
