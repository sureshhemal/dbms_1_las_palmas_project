<?php

use App\Clinical\Entities\Models\PerformedProcedure;
use App\Clinical\Entities\Models\Prescription;
use App\Clinical\Entities\Models\Procedure;
use App\Staff\Entities\Models\Physician;

beforeEach(function () {
    $this->travelTo('2026-10-15 12:00:00');
});

function procedureBy(Physician $physician, string $procDate, ?Procedure $procedure = null): PerformedProcedure
{
    return PerformedProcedure::factory()
        ->for($physician)
        ->for($procedure ?? Procedure::factory())
        ->performedOn($procDate)
        ->create();
}

it('returns 401 to guests', function () {
    $response = $this->getJson(route('api.physicians.summary.show', Physician::factory()->create()));

    $response->assertUnauthorized();
});

it('shows the next procedure scheduled for the physician', function () {
    $physician = Physician::factory()->create();
    procedureBy($physician, '2026-10-15');
    procedureBy($physician, '2026-11-02');
    $next = procedureBy($physician, '2026-10-18', Procedure::factory()->create(['name' => 'Knee Arthroscopy']));
    procedureBy(Physician::factory()->create(), '2026-10-16');

    $response = $this->actingAsLoggedInUser()->getJson(route('api.physicians.summary.show', $physician));

    $response->assertOk();
    expect($response->json('nextProcedure'))->toMatchArray(['id' => $next->id, 'procDate' => '2026-10-18']);
    expect($response->json('nextProcedure.procedure'))->toBe(['id' => $next->procedure_id, 'name' => 'Knee Arthroscopy']);
});

it('counts the procedures the physician performed and the prescriptions they issued', function () {
    $physician = Physician::factory()->create();
    procedureBy($physician, '2026-10-02');
    procedureBy($physician, '2026-10-15');
    procedureBy($physician, '2026-10-16');
    procedureBy(Physician::factory()->create(), '2026-10-05');
    Prescription::factory()->for($physician)->count(3)->create();
    Prescription::factory()->create();

    $response = $this->actingAsLoggedInUser()->getJson(route('api.physicians.summary.show', $physician));

    expect($response->json('performedProceduresCount'))->toBe(2);
    expect($response->json('prescriptionsCount'))->toBe(3);
});

it('totals the revenue from procedures the physician already performed', function () {
    $physician = Physician::factory()->create();
    $biopsy = Procedure::factory()->create(['cost' => 1200.50]);
    procedureBy($physician, '2026-10-02', $biopsy);
    procedureBy($physician, '2026-10-15', $biopsy);
    procedureBy($physician, '2026-10-11', Procedure::factory()->create(['cost' => null]));
    procedureBy($physician, '2026-10-16', Procedure::factory()->create(['cost' => 9000]));
    procedureBy(Physician::factory()->create(), '2026-10-05', $biopsy);

    $response = $this->actingAsLoggedInUser()->getJson(route('api.physicians.summary.show', $physician));

    expect($response->json('totalRevenue'))->toEqual(2401);
});

it('shows zeros for a physician with no procedures or prescriptions', function () {
    $response = $this->actingAsLoggedInUser()->getJson(route('api.physicians.summary.show', Physician::factory()->create()));

    $response->assertOk()->assertExactJson([
        'nextProcedure' => null,
        'performedProceduresCount' => 0,
        'prescriptionsCount' => 0,
        'totalRevenue' => 0,
    ]);
});

it('returns 404 for an offboarded physician', function () {
    $physician = Physician::factory()->create();
    $physician->delete();

    $response = $this->actingAsLoggedInUser()->getJson(route('api.physicians.summary.show', $physician));

    $response->assertNotFound();
});
