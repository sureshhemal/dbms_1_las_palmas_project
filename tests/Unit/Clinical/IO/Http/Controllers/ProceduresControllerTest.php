<?php

use App\Clinical\Entities\Models\PerformedProcedure;
use App\Clinical\Entities\Models\Procedure;

it('returns 401 to guests', function () {
    $response = $this->getJson(route('api.procedures.index'));

    $response->assertUnauthorized();
});

it('lists the procedures on the first page in name order', function () {
    Procedure::factory()->create(['name' => 'Colonoscopy']);
    Procedure::factory()->create(['name' => 'Appendectomy']);
    Procedure::factory()->create(['name' => 'Knee Replacement']);

    $response = $this->actingAsLoggedInUser()->getJson(route('api.procedures.index'));

    $response->assertOk()->assertJsonCount(3, 'data')->assertJson(['current_page' => 1, 'per_page' => 20, 'total' => 3]);
    expect($response->json('data.*.name'))->toBe(['Appendectomy', 'Colonoscopy', 'Knee Replacement']);
});

it('returns the requested page of procedures', function () {
    foreach (range(1, 25) as $number) {
        Procedure::factory()->create(['name' => sprintf('Procedure %02d', $number)]);
    }

    $response = $this->actingAsLoggedInUser()->getJson(route('api.procedures.index', ['page' => 2, 'per_page' => 10]));

    $response->assertOk()->assertJson([
        'current_page' => 2,
        'per_page' => 10,
        'from' => 11,
        'to' => 20,
        'total' => 25,
        'last_page' => 3,
    ]);
    expect($response->json('data.0.name'))->toBe('Procedure 11');
});

it('shows the share of all performed procedures each procedure makes up', function () {
    $this->travelTo('2026-10-15 12:00:00');
    $appendectomy = Procedure::factory()->create(['name' => 'Appendectomy']);
    $biopsy = Procedure::factory()->create(['name' => 'Biopsy']);
    Procedure::factory()->create(['name' => 'Colonoscopy']);
    $removed = Procedure::factory()->create(['name' => 'Lobotomy']);
    PerformedProcedure::factory()->for($appendectomy)->performedOn('2026-10-01')->count(3)->create();
    PerformedProcedure::factory()->for($appendectomy)->performedOn('2026-10-16')->create();
    PerformedProcedure::factory()->for($biopsy)->performedOn('2026-10-15')->create();
    PerformedProcedure::factory()->for($removed)->performedOn('2026-10-02')->create();
    $removed->delete();

    $response = $this->actingAsLoggedInUser()->getJson(route('api.procedures.index', ['sort' => 'name']));

    expect(collect($response->json('data'))->map(fn (array $procedure): array => [
        $procedure['name'],
        $procedure['performedCount'],
        $procedure['performedShare'],
    ])->all())->toEqual([
        ['Appendectomy', 3, 60.0],
        ['Biopsy', 1, 20.0],
        ['Colonoscopy', 0, 0.0],
    ]);
});

it('rounds the performed share to two decimal places', function () {
    $this->travelTo('2026-10-15 12:00:00');
    $biopsy = Procedure::factory()->create(['name' => 'Biopsy']);
    PerformedProcedure::factory()->for($biopsy)->performedOn('2026-10-01')->create();
    PerformedProcedure::factory()->performedOn('2026-10-01')->count(2)->create();

    $response = $this->actingAsLoggedInUser()->getJson(route('api.procedures.index', ['search' => 'Biopsy']));

    expect($response->json('data.0.performedShare'))->toEqual(33.33);
});

it('shows a zero share when no procedure has been performed yet', function () {
    Procedure::factory()->create();

    $response = $this->actingAsLoggedInUser()->getJson(route('api.procedures.index'));

    expect($response->json('data.0.performedShare'))->toBe(0);
});

it('counts each procedure once in the page details however often it was performed', function () {
    $this->travelTo('2026-10-15 12:00:00');
    $appendectomy = Procedure::factory()->create(['name' => 'Appendectomy']);
    Procedure::factory()->create(['name' => 'Biopsy']);
    PerformedProcedure::factory()->for($appendectomy)->performedOn('2026-10-01')->count(5)->create();

    $response = $this->actingAsLoggedInUser()->getJson(route('api.procedures.index', ['per_page' => 10, 'sort' => 'name']));

    $response->assertJson(['total' => 2, 'last_page' => 1]);
    expect($response->json('data.*.name'))->toBe(['Appendectomy', 'Biopsy']);
});

it('sorts the procedures by name in the chosen direction', function () {
    Procedure::factory()->create(['name' => 'Biopsy']);
    Procedure::factory()->create(['name' => 'Appendectomy']);
    Procedure::factory()->create(['name' => 'Colonoscopy']);

    $response = $this->actingAsLoggedInUser()->getJson(route('api.procedures.index', ['sort' => 'name', 'direction' => 'desc']));

    expect($response->json('data.*.name'))->toBe(['Colonoscopy', 'Biopsy', 'Appendectomy']);
});

it('sorts the procedures by how often they were performed, breaking ties by name', function (string $direction, array $expectedNames) {
    $this->travelTo('2026-10-15 12:00:00');
    $biopsy = Procedure::factory()->create(['name' => 'Biopsy']);
    $appendectomy = Procedure::factory()->create(['name' => 'Appendectomy']);
    $colonoscopy = Procedure::factory()->create(['name' => 'Colonoscopy']);
    Procedure::factory()->create(['name' => 'Tonsillectomy']);
    PerformedProcedure::factory()->for($biopsy)->performedOn('2026-10-01')->count(3)->create();
    PerformedProcedure::factory()->for($appendectomy)->performedOn('2026-10-01')->create();
    PerformedProcedure::factory()->for($colonoscopy)->performedOn('2026-10-01')->create();

    $response = $this->actingAsLoggedInUser()->getJson(route('api.procedures.index', ['sort' => 'performedShare', 'direction' => $direction]));

    expect($response->json('data.*.name'))->toBe($expectedNames);
})->with([
    'ascending' => ['asc', ['Tonsillectomy', 'Appendectomy', 'Colonoscopy', 'Biopsy']],
    'descending' => ['desc', ['Biopsy', 'Appendectomy', 'Colonoscopy', 'Tonsillectomy']],
]);

it('sorts by name ascending when the sort column or direction is unknown', function () {
    Procedure::factory()->create(['name' => 'Biopsy']);
    Procedure::factory()->create(['name' => 'Appendectomy']);

    $response = $this->actingAsLoggedInUser()->getJson(route('api.procedures.index', ['sort' => 'cost', 'direction' => 'sideways']));

    expect($response->json('data.*.name'))->toBe(['Appendectomy', 'Biopsy']);
});

it('leaves out removed procedures', function () {
    $keptProcedure = Procedure::factory()->create();
    Procedure::factory()->create()->delete();

    $response = $this->actingAsLoggedInUser()->getJson(route('api.procedures.index'));

    expect($response->json('data.*.id'))->toBe([$keptProcedure->id]);
});

it('finds procedures by name', function () {
    $appendectomy = Procedure::factory()->create(['name' => 'Appendectomy']);
    Procedure::factory()->create(['name' => 'Colonoscopy']);

    $response = $this->actingAsLoggedInUser()->getJson(route('api.procedures.index', ['search' => 'append']));

    expect($response->json('data.*.id'))->toBe([$appendectomy->id]);
    expect($response->json('total'))->toBe(1);
});

it('finds procedures by procedure id', function () {
    $procedure = Procedure::factory()->create(['procID' => 4321]);
    Procedure::factory()->create(['procID' => 9876]);

    $response = $this->actingAsLoggedInUser()->getJson(route('api.procedures.index', ['search' => '432']));

    expect($response->json('data.*.id'))->toBe([$procedure->id]);
});

it('returns 401 to guests adding a procedure', function () {
    $response = $this->postJson(route('api.procedures.store'), ['name' => 'Appendectomy', 'cost' => 1500]);

    $response->assertUnauthorized();
    $this->assertDatabaseEmpty('Procedure');
});

it('adds a procedure', function () {
    $response = $this->actingAsLoggedInUser()->postJson(route('api.procedures.store'), [
        'procID' => 4321,
        'name' => 'Appendectomy',
        'cost' => 1500.5,
    ]);

    $response->assertCreated()->assertJson(['procID' => 4321, 'name' => 'Appendectomy', 'cost' => 1500.5]);
    $this->assertDatabaseHas('Procedure', ['procID' => 4321, 'name' => 'Appendectomy', 'cost' => 1500.5]);
});

it('adds a procedure without a cost when the price is not set yet', function () {
    $response = $this->actingAsLoggedInUser()->postJson(route('api.procedures.store'), [
        'name' => 'Appendectomy',
        'cost' => '',
    ]);

    $response->assertCreated()->assertJson(['name' => 'Appendectomy', 'cost' => null]);
    $this->assertDatabaseHas('Procedure', ['name' => 'Appendectomy', 'cost' => null]);
});

it('assigns the procedure id when it is left blank', function () {
    Procedure::factory()->create(['procID' => 4321]);

    $response = $this->actingAsLoggedInUser()->postJson(route('api.procedures.store'), [
        'procID' => '',
        'name' => 'Appendectomy',
    ]);

    $response->assertCreated();
    expect($response->json('procID'))->toBeInt()->toBeGreaterThan(4321);
});

it('shows the MySQL error when the procedure id is already taken', function () {
    config(['app.debug' => true]);
    Procedure::factory()->create(['procID' => 4321]);

    $response = $this->actingAsLoggedInUser()->postJson(route('api.procedures.store'), [
        'procID' => 4321,
        'name' => 'Appendectomy',
    ]);

    $response->assertInternalServerError();
    expect($response->json('message'))->toContain("Duplicate entry '4321'");
    $this->assertDatabaseCount('Procedure', 1);
});

it('shows the MySQL error when the name is left blank', function () {
    config(['app.debug' => true]);

    $response = $this->actingAsLoggedInUser()->postJson(route('api.procedures.store'), [
        'name' => '',
        'cost' => 1500,
    ]);

    $response->assertInternalServerError();
    expect($response->json('message'))->toContain("Column 'name' cannot be null");
    $this->assertDatabaseEmpty('Procedure');
});

it('shows the MySQL error when the name is longer than 40 characters', function () {
    config(['app.debug' => true]);

    $response = $this->actingAsLoggedInUser()->postJson(route('api.procedures.store'), [
        'name' => str_repeat('a', 41),
    ]);

    $response->assertInternalServerError();
    expect($response->json('message'))->toContain("Data too long for column 'name'");
    $this->assertDatabaseEmpty('Procedure');
});

it('returns 401 to guests updating a procedure', function () {
    $procedure = Procedure::factory()->create(['name' => 'Appendectomy']);

    $response = $this->putJson(route('api.procedures.update', $procedure), ['name' => 'Colonoscopy']);

    $response->assertUnauthorized();
    expect($procedure->fresh()->name)->toBe('Appendectomy');
});

it('updates a procedure', function () {
    $procedure = Procedure::factory()->create(['procID' => 4321, 'name' => 'Appendectomy', 'cost' => 1500]);

    $response = $this->actingAsLoggedInUser()->putJson(route('api.procedures.update', $procedure), [
        'procID' => 1234,
        'name' => 'Laparoscopic Appendectomy',
        'cost' => 2750.25,
    ]);

    $response->assertOk()->assertJson(['id' => $procedure->id, 'procID' => 1234, 'name' => 'Laparoscopic Appendectomy', 'cost' => 2750.25]);
    $this->assertDatabaseHas('Procedure', ['id' => $procedure->id, 'procID' => 1234, 'name' => 'Laparoscopic Appendectomy', 'cost' => 2750.25]);
});

it('keeps the procedure id and clears the cost when they are left blank', function () {
    $procedure = Procedure::factory()->create(['procID' => 4321, 'name' => 'Appendectomy', 'cost' => 1500]);

    $response = $this->actingAsLoggedInUser()->putJson(route('api.procedures.update', $procedure), [
        'procID' => '',
        'name' => 'Appendectomy',
        'cost' => '',
    ]);

    $response->assertOk();
    $this->assertDatabaseHas('Procedure', ['id' => $procedure->id, 'procID' => 4321, 'cost' => null]);
});

it('shows the MySQL error when updating to a procedure id that is already taken', function () {
    config(['app.debug' => true]);
    Procedure::factory()->create(['procID' => 4321]);
    $procedure = Procedure::factory()->create(['procID' => 1234]);

    $response = $this->actingAsLoggedInUser()->putJson(route('api.procedures.update', $procedure), [
        'procID' => 4321,
        'name' => 'Appendectomy',
    ]);

    $response->assertInternalServerError();
    expect($response->json('message'))->toContain("Duplicate entry '4321'");
    expect($procedure->fresh()->procID)->toBe(1234);
});

it('returns 401 to guests removing a procedure', function () {
    $procedure = Procedure::factory()->create();

    $response = $this->deleteJson(route('api.procedures.destroy', $procedure));

    $response->assertUnauthorized();
    $this->assertNotSoftDeleted($procedure);
});

it('removes a procedure by soft deleting it', function () {
    $procedure = Procedure::factory()->create();

    $response = $this->actingAsLoggedInUser()->deleteJson(route('api.procedures.destroy', $procedure));

    $response->assertNoContent();
    $this->assertSoftDeleted($procedure);
});

it('returns 404 when removing a procedure that is already removed', function () {
    $procedure = Procedure::factory()->create();
    $procedure->delete();

    $response = $this->actingAsLoggedInUser()->deleteJson(route('api.procedures.destroy', $procedure));

    $response->assertNotFound();
});
