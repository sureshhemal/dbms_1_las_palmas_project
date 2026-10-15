<?php

use App\Staff\Entities\Models\Nurse;
use App\Staff\Entities\NursePosition;

it('returns 401 to guests', function () {
    $response = $this->getJson(route('api.nurses.index'));

    $response->assertUnauthorized();
});

it('lists the nurses on the first page', function () {
    $nurses = Nurse::factory()->count(3)->create();

    $response = $this->actingAsLoggedInUser()->getJson(route('api.nurses.index'));

    $response->assertOk()->assertJsonCount(3, 'data')->assertJson(['current_page' => 1, 'total' => 3]);
    expect($response->json('data.*.id'))->toEqualCanonicalizing($nurses->pluck('id')->all());
});

it('leaves out soft-deleted nurses', function () {
    $keptNurse = Nurse::factory()->create();
    Nurse::factory()->create()->delete();

    $response = $this->actingAsLoggedInUser()->getJson(route('api.nurses.index'));

    expect($response->json('data.*.id'))->toBe([$keptNurse->id]);
});

it('returns the requested page of nurses, newest first', function () {
    foreach (range(1, 25) as $nurseID) {
        Nurse::factory()->create(['nurseID' => $nurseID]);
    }

    $response = $this->actingAsLoggedInUser()->getJson(route('api.nurses.index', ['page' => 2, 'per_page' => 10]));

    $response->assertOk()->assertJson([
        'current_page' => 2,
        'per_page' => 10,
        'from' => 11,
        'to' => 20,
        'total' => 25,
        'last_page' => 3,
    ]);
    expect($response->json('data.*.nurseID'))->toBe(range(15, 6));
});

it('lists the most recently added nurses first', function () {
    $this->travel(-1)->days();
    $olderNurse = Nurse::factory()->create();
    $this->travelBack();
    $newerNurse = Nurse::factory()->create();

    $response = $this->actingAsLoggedInUser()->getJson(route('api.nurses.index'));

    expect($response->json('data.*.id'))->toBe([$newerNurse->id, $olderNurse->id]);
});

it('finds nurses by name', function () {
    $carla = Nurse::factory()->create(['name' => 'Carla Espinosa']);
    Nurse::factory()->create(['name' => 'Laverne Roberts']);

    $response = $this->actingAsLoggedInUser()->getJson(route('api.nurses.index', ['search' => 'carla']));

    expect($response->json('data.*.id'))->toBe([$carla->id]);
    expect($response->json('total'))->toBe(1);
});

it('finds nurses by nurse id', function () {
    $nurse = Nurse::factory()->create(['nurseID' => 12345]);
    Nurse::factory()->create(['nurseID' => 67890]);

    $response = $this->actingAsLoggedInUser()->getJson(route('api.nurses.index', ['search' => '123']));

    expect($response->json('data.*.id'))->toBe([$nurse->id]);
});

it('filters nurses by the chosen positions', function () {
    $headNurse = Nurse::factory()->withPosition(NursePosition::HeadNurse)->create();
    Nurse::factory()->withPosition(NursePosition::Nurse)->create();

    $response = $this->actingAsLoggedInUser()->getJson(route('api.nurses.index', ['positions' => ['Head Nurse']]));

    expect($response->json('data.*.id'))->toBe([$headNurse->id]);
});

it('ignores positions that do not exist', function () {
    Nurse::factory()->count(2)->create();

    $response = $this->actingAsLoggedInUser()->getJson(route('api.nurses.index', ['positions' => ['Janitor']]));

    $response->assertJsonCount(2, 'data');
});

it('combines the search and the position filter', function () {
    $headNurseCarla = Nurse::factory()->withPosition(NursePosition::HeadNurse)->create(['name' => 'Carla Espinosa']);
    Nurse::factory()->withPosition(NursePosition::Nurse)->create(['name' => 'Carla Roberts']);
    Nurse::factory()->withPosition(NursePosition::HeadNurse)->create(['name' => 'Laverne Roberts']);

    $response = $this->actingAsLoggedInUser()->getJson(route('api.nurses.index', [
        'search' => 'carla',
        'positions' => ['Head Nurse'],
    ]));

    expect($response->json('data.*.id'))->toBe([$headNurseCarla->id]);
});

it('returns 401 to guests adding a nurse', function () {
    $response = $this->postJson(route('api.nurses.store'), [
        'name' => 'Carla Espinosa',
        'position' => 'Nurse',
    ]);

    $response->assertUnauthorized();
    $this->assertDatabaseEmpty('Nurse');
});

it('adds a nurse', function () {
    $response = $this->actingAsLoggedInUser()->postJson(route('api.nurses.store'), [
        'nurseID' => 12345,
        'name' => 'Carla Espinosa',
        'position' => 'Head Nurse',
        'ssn' => '012345678',
    ]);

    $response->assertCreated()->assertJson(['nurseID' => 12345, 'name' => 'Carla Espinosa']);
    $this->assertDatabaseHas('Nurse', [
        'nurseID' => 12345,
        'name' => 'Carla Espinosa',
        'position' => NursePosition::HeadNurse->value,
        'ssn' => '012345678',
    ]);
});

it('assigns the nurse id when it is left blank', function () {
    $response = $this->actingAsLoggedInUser()->postJson(route('api.nurses.store'), [
        'nurseID' => '',
        'name' => 'Carla Espinosa',
        'position' => 'Nurse',
        'ssn' => '',
    ]);

    $response->assertCreated();
    expect($response->json('nurseID'))->toBeInt();
    $this->assertDatabaseHas('Nurse', ['name' => 'Carla Espinosa', 'ssn' => null]);
});

it('shows the MySQL error when the nurse id is already taken', function () {
    config(['app.debug' => true]);
    Nurse::factory()->create(['nurseID' => 12345]);

    $response = $this->actingAsLoggedInUser()->postJson(route('api.nurses.store'), [
        'nurseID' => 12345,
        'name' => 'Carla Espinosa',
        'position' => 'Nurse',
    ]);

    $response->assertInternalServerError();
    expect($response->json('message'))->toContain("Duplicate entry '12345'");
    $this->assertDatabaseCount('Nurse', 1);
});

it('returns 401 to guests updating a nurse', function () {
    $nurse = Nurse::factory()->create(['name' => 'Carla Espinosa']);

    $response = $this->putJson(route('api.nurses.update', $nurse), [
        'name' => 'Laverne Roberts',
        'position' => 'Nurse',
    ]);

    $response->assertUnauthorized();
    expect($nurse->fresh()->name)->toBe('Carla Espinosa');
});

it('updates a nurse', function () {
    $nurse = Nurse::factory()->withPosition(NursePosition::Nurse)->create(['nurseID' => 12345]);

    $response = $this->actingAsLoggedInUser()->putJson(route('api.nurses.update', $nurse), [
        'nurseID' => 54321,
        'name' => 'Carla Espinosa',
        'position' => 'Head Nurse',
        'ssn' => '012345678',
    ]);

    $response->assertOk()->assertJson(['id' => $nurse->id, 'nurseID' => 54321, 'name' => 'Carla Espinosa']);
    $this->assertDatabaseHas('Nurse', [
        'id' => $nurse->id,
        'nurseID' => 54321,
        'name' => 'Carla Espinosa',
        'position' => NursePosition::HeadNurse->value,
        'ssn' => '012345678',
    ]);
});

it('keeps the nurse id and clears the ssn when they are left blank', function () {
    $nurse = Nurse::factory()->create(['nurseID' => 12345, 'ssn' => '012345678']);

    $response = $this->actingAsLoggedInUser()->putJson(route('api.nurses.update', $nurse), [
        'nurseID' => '',
        'name' => 'Carla Espinosa',
        'position' => 'Nurse',
        'ssn' => '',
    ]);

    $response->assertOk();
    $this->assertDatabaseHas('Nurse', ['id' => $nurse->id, 'nurseID' => 12345, 'ssn' => null]);
});

it('shows the MySQL error when updating to a nurse id that is already taken', function () {
    config(['app.debug' => true]);
    Nurse::factory()->create(['nurseID' => 12345]);
    $nurse = Nurse::factory()->create(['nurseID' => 54321]);

    $response = $this->actingAsLoggedInUser()->putJson(route('api.nurses.update', $nurse), [
        'nurseID' => 12345,
        'name' => 'Carla Espinosa',
        'position' => 'Nurse',
    ]);

    $response->assertInternalServerError();
    expect($response->json('message'))->toContain("Duplicate entry '12345'");
    expect($nurse->fresh()->nurseID)->toBe(54321);
});
