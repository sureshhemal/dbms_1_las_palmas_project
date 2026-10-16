<?php

use App\Clinical\Entities\Models\PerformedProcedure;
use App\Clinical\Entities\Models\Procedure;
use App\Staff\Entities\Models\Physician;

beforeEach(function () {
    $this->travelTo('2026-10-15 12:00:00');
});

function performedBy(Physician $physician, Procedure $procedure, string $procDate = '2026-10-01', int $times = 1): void
{
    collect(range(1, $times))->each(fn () => PerformedProcedure::factory()->for($physician)->for($procedure)->performedOn($procDate)->create());
}

it('returns 401 to guests', function () {
    $response = $this->getJson(route('api.physicians.procedures.index', Physician::factory()->create()));

    $response->assertUnauthorized();
});

it('groups the procedures the physician performed with their share, most performed first', function () {
    $physician = Physician::factory()->create();
    $biopsy = Procedure::factory()->create(['name' => 'Biopsy']);
    $appendectomy = Procedure::factory()->create(['name' => 'Appendectomy']);
    $colonoscopy = Procedure::factory()->create(['name' => 'Colonoscopy']);
    performedBy($physician, $biopsy, times: 2);
    performedBy($physician, $colonoscopy);
    performedBy($physician, $appendectomy, '2026-10-15');
    performedBy(Physician::factory()->create(), $biopsy, times: 3);

    $response = $this->actingAsLoggedInUser()->getJson(route('api.physicians.procedures.index', $physician));

    $response->assertOk()->assertExactJson([
        ['procedureId' => $biopsy->id, 'name' => 'Biopsy', 'isRemoved' => false, 'performedCount' => 2, 'percentage' => 50],
        ['procedureId' => $appendectomy->id, 'name' => 'Appendectomy', 'isRemoved' => false, 'performedCount' => 1, 'percentage' => 25],
        ['procedureId' => $colonoscopy->id, 'name' => 'Colonoscopy', 'isRemoved' => false, 'performedCount' => 1, 'percentage' => 25],
    ]);
});

it('leaves out procedures scheduled for later', function () {
    $physician = Physician::factory()->create();
    $biopsy = Procedure::factory()->create(['name' => 'Biopsy']);
    performedBy($physician, $biopsy);
    performedBy($physician, Procedure::factory()->create(['name' => 'Colonoscopy']), '2026-10-16');

    $response = $this->actingAsLoggedInUser()->getJson(route('api.physicians.procedures.index', $physician));

    expect($response->json())->toHaveCount(1);
    expect($response->json('0'))->toMatchArray(['name' => 'Biopsy', 'performedCount' => 1, 'percentage' => 100]);
});

it('rounds each share to one decimal place', function () {
    $physician = Physician::factory()->create();
    performedBy($physician, Procedure::factory()->create(['name' => 'Biopsy']), times: 2);
    performedBy($physician, Procedure::factory()->create(['name' => 'Colonoscopy']));

    $response = $this->actingAsLoggedInUser()->getJson(route('api.physicians.procedures.index', $physician));

    expect($response->json('*.percentage'))->toEqual([66.7, 33.3]);
});

it('still lists a procedure that has since been removed', function () {
    $physician = Physician::factory()->create();
    $removed = Procedure::factory()->create(['name' => 'Lobotomy']);
    performedBy($physician, $removed);
    $removed->delete();

    $response = $this->actingAsLoggedInUser()->getJson(route('api.physicians.procedures.index', $physician));

    expect($response->json('0'))->toMatchArray(['name' => 'Lobotomy', 'isRemoved' => true, 'performedCount' => 1]);
});

it('returns an empty list for a physician who has not performed any procedure', function () {
    $response = $this->actingAsLoggedInUser()->getJson(route('api.physicians.procedures.index', Physician::factory()->create()));

    $response->assertOk()->assertExactJson([]);
});

it('returns 404 for an offboarded physician', function () {
    $physician = Physician::factory()->create();
    $physician->delete();

    $response = $this->actingAsLoggedInUser()->getJson(route('api.physicians.procedures.index', $physician));

    $response->assertNotFound();
});
