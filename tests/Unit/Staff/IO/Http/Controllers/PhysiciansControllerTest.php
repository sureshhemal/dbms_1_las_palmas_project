<?php

use App\Staff\Entities\Models\Physician;
use App\Staff\Entities\PhysicianPosition;

it('returns 401 to guests', function () {
    $response = $this->getJson(route('api.physicians.index'));

    $response->assertUnauthorized();
});

it('lists all physicians', function () {
    $physicians = Physician::factory()->count(3)->create();

    $response = $this->actingAsLoggedInUser()->getJson(route('api.physicians.index'));

    $response->assertOk()->assertJsonCount(3);
    expect($response->json('*.id'))->toEqualCanonicalizing($physicians->pluck('id')->all());
});

it('leaves out soft-deleted physicians', function () {
    $keptPhysician = Physician::factory()->create();
    Physician::factory()->create()->delete();

    $response = $this->actingAsLoggedInUser()->getJson(route('api.physicians.index'));

    expect($response->json('*.id'))->toBe([$keptPhysician->id]);
});

it('lists the most recently added physicians first', function () {
    $this->travel(-1)->days();
    $olderPhysician = Physician::factory()->create();
    $this->travelBack();
    $newerPhysician = Physician::factory()->create();

    $response = $this->actingAsLoggedInUser()->getJson(route('api.physicians.index'));

    expect($response->json('*.id'))->toBe([$newerPhysician->id, $olderPhysician->id]);
});

it('finds physicians by name', function () {
    $perryCox = Physician::factory()->create(['name' => 'Perry Cox']);
    Physician::factory()->create(['name' => 'John Dorian']);

    $response = $this->actingAsLoggedInUser()->getJson(route('api.physicians.index', ['search' => 'perry']));

    expect($response->json('*.id'))->toBe([$perryCox->id]);
});

it('finds physicians by physician id', function () {
    $physician = Physician::factory()->create(['physicianID' => 12345]);
    Physician::factory()->create(['physicianID' => 67890]);

    $response = $this->actingAsLoggedInUser()->getJson(route('api.physicians.index', ['search' => '123']));

    expect($response->json('*.id'))->toBe([$physician->id]);
});

it('filters physicians by the chosen positions', function () {
    $surgeon = Physician::factory()->withPosition(PhysicianPosition::Surgeon)->create();
    $psychiatrist = Physician::factory()->withPosition(PhysicianPosition::Psychiatrist)->create();
    Physician::factory()->withPosition(PhysicianPosition::Intern)->create();

    $response = $this->actingAsLoggedInUser()->getJson(route('api.physicians.index', [
        'positions' => ['Surgeon', 'Psychiatrist'],
    ]));

    expect($response->json('*.id'))->toEqualCanonicalizing([$surgeon->id, $psychiatrist->id]);
});

it('ignores physician positions that do not exist', function () {
    Physician::factory()->count(2)->create();

    $response = $this->actingAsLoggedInUser()->getJson(route('api.physicians.index', ['positions' => ['Janitor']]));

    $response->assertJsonCount(2);
});

it('combines the physician search and the position filter', function () {
    $surgeonPerry = Physician::factory()->withPosition(PhysicianPosition::Surgeon)->create(['name' => 'Perry Cox']);
    Physician::factory()->withPosition(PhysicianPosition::Senior)->create(['name' => 'Perry Kelso']);
    Physician::factory()->withPosition(PhysicianPosition::Surgeon)->create(['name' => 'Chris Turk']);

    $response = $this->actingAsLoggedInUser()->getJson(route('api.physicians.index', [
        'search' => 'perry',
        'positions' => ['Surgeon'],
    ]));

    expect($response->json('*.id'))->toBe([$surgeonPerry->id]);
});

it('returns 401 to guests adding a physician', function () {
    $response = $this->postJson(route('api.physicians.store'), [
        'name' => 'Perry Cox',
        'position' => 'Senior',
    ]);

    $response->assertUnauthorized();
    $this->assertDatabaseEmpty('Physician');
});

it('adds a physician', function () {
    $response = $this->actingAsLoggedInUser()->postJson(route('api.physicians.store'), [
        'physicianID' => 12345,
        'name' => 'Perry Cox',
        'position' => 'Chief of Medicine',
        'ssn' => '012345678',
    ]);

    $response->assertCreated()->assertJson(['physicianID' => 12345, 'name' => 'Perry Cox']);
    $this->assertDatabaseHas('Physician', [
        'physicianID' => 12345,
        'name' => 'Perry Cox',
        'position' => PhysicianPosition::ChiefOfMedicine->value,
        'ssn' => '012345678',
    ]);
});

it('assigns the physician id when it is left blank', function () {
    $response = $this->actingAsLoggedInUser()->postJson(route('api.physicians.store'), [
        'physicianID' => '',
        'name' => 'Perry Cox',
        'position' => 'Senior',
        'ssn' => '',
    ]);

    $response->assertCreated();
    expect($response->json('physicianID'))->toBeInt();
    $this->assertDatabaseHas('Physician', ['name' => 'Perry Cox', 'ssn' => null]);
});

it('shows the MySQL error when the physician id is already taken', function () {
    config(['app.debug' => true]);
    Physician::factory()->create(['physicianID' => 12345]);

    $response = $this->actingAsLoggedInUser()->postJson(route('api.physicians.store'), [
        'physicianID' => 12345,
        'name' => 'Perry Cox',
        'position' => 'Senior',
    ]);

    $response->assertInternalServerError();
    expect($response->json('message'))->toContain("Duplicate entry '12345'");
    $this->assertDatabaseCount('Physician', 1);
});

it('returns 401 to guests updating a physician', function () {
    $physician = Physician::factory()->create(['name' => 'Perry Cox']);

    $response = $this->putJson(route('api.physicians.update', $physician), [
        'name' => 'Bob Kelso',
        'position' => 'Senior',
    ]);

    $response->assertUnauthorized();
    expect($physician->fresh()->name)->toBe('Perry Cox');
});

it('updates a physician', function () {
    $physician = Physician::factory()->withPosition(PhysicianPosition::Resident)->create(['physicianID' => 12345]);

    $response = $this->actingAsLoggedInUser()->putJson(route('api.physicians.update', $physician), [
        'physicianID' => 54321,
        'name' => 'Perry Cox',
        'position' => 'Chief of Medicine',
        'ssn' => '012345678',
    ]);

    $response->assertOk()->assertJson(['id' => $physician->id, 'physicianID' => 54321, 'name' => 'Perry Cox']);
    $this->assertDatabaseHas('Physician', [
        'id' => $physician->id,
        'physicianID' => 54321,
        'name' => 'Perry Cox',
        'position' => PhysicianPosition::ChiefOfMedicine->value,
        'ssn' => '012345678',
    ]);
});

it('keeps the physician id and clears the ssn when they are left blank', function () {
    $physician = Physician::factory()->create(['physicianID' => 12345, 'ssn' => '012345678']);

    $response = $this->actingAsLoggedInUser()->putJson(route('api.physicians.update', $physician), [
        'physicianID' => '',
        'name' => 'Perry Cox',
        'position' => 'Senior',
        'ssn' => '',
    ]);

    $response->assertOk();
    $this->assertDatabaseHas('Physician', ['id' => $physician->id, 'physicianID' => 12345, 'ssn' => null]);
});

it('shows the MySQL error when updating to a physician id that is already taken', function () {
    config(['app.debug' => true]);
    Physician::factory()->create(['physicianID' => 12345]);
    $physician = Physician::factory()->create(['physicianID' => 54321]);

    $response = $this->actingAsLoggedInUser()->putJson(route('api.physicians.update', $physician), [
        'physicianID' => 12345,
        'name' => 'Perry Cox',
        'position' => 'Senior',
    ]);

    $response->assertInternalServerError();
    expect($response->json('message'))->toContain("Duplicate entry '12345'");
    expect($physician->fresh()->physicianID)->toBe(54321);
});
