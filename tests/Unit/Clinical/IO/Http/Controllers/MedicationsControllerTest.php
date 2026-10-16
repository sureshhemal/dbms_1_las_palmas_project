<?php

use App\Clinical\Entities\Models\Medication;
use App\Clinical\Entities\Models\Prescription;

it('returns 401 to guests', function () {
    $response = $this->getJson(route('api.medications.index'));

    $response->assertUnauthorized();
});

it('lists the medications on the first page in name order', function () {
    Medication::factory()->create(['name' => 'Gabapentin']);
    Medication::factory()->create(['name' => 'Amoxicillin']);
    Medication::factory()->create(['name' => 'Metformin Hydrochloride']);

    $response = $this->actingAsLoggedInUser()->getJson(route('api.medications.index'));

    $response->assertOk()->assertJsonCount(3, 'data')->assertJson(['current_page' => 1, 'per_page' => 20, 'total' => 3]);
    expect($response->json('data.*.name'))->toBe(['Amoxicillin', 'Gabapentin', 'Metformin Hydrochloride']);
});

it('returns the requested page of medications', function () {
    foreach (range(1, 25) as $number) {
        Medication::factory()->create(['name' => sprintf('Medication %02d', $number)]);
    }

    $response = $this->actingAsLoggedInUser()->getJson(route('api.medications.index', ['page' => 2, 'per_page' => 10]));

    $response->assertOk()->assertJson([
        'current_page' => 2,
        'per_page' => 10,
        'from' => 11,
        'to' => 20,
        'total' => 25,
        'last_page' => 3,
    ]);
    expect($response->json('data.0.name'))->toBe('Medication 11');
});

it('shows the share of all prescriptions each medication was prescribed in', function () {
    $amoxicillin = Medication::factory()->create(['name' => 'Amoxicillin']);
    $gabapentin = Medication::factory()->create(['name' => 'Gabapentin']);
    Medication::factory()->create(['name' => 'Metformin Hydrochloride']);
    $removed = Medication::factory()->create(['name' => 'Ranitidine']);
    Prescription::factory()->for($amoxicillin)->count(3)->create();
    Prescription::factory()->for($gabapentin)->create();
    Prescription::factory()->for($removed)->create();
    $removed->delete();

    $response = $this->actingAsLoggedInUser()->getJson(route('api.medications.index'));

    expect(collect($response->json('data'))->map(fn (array $medication): array => [
        $medication['name'],
        $medication['prescriptionsCount'],
        $medication['prescriptionShare'],
    ])->all())->toEqual([
        ['Amoxicillin', 3, 60.0],
        ['Gabapentin', 1, 20.0],
        ['Metformin Hydrochloride', 0, 0.0],
    ]);
});

it('rounds the prescription share to two decimal places', function () {
    $amoxicillin = Medication::factory()->create(['name' => 'Amoxicillin']);
    Prescription::factory()->for($amoxicillin)->create();
    Prescription::factory()->count(2)->create();

    $response = $this->actingAsLoggedInUser()->getJson(route('api.medications.index', ['search' => 'Amoxicillin']));

    expect($response->json('data.0.prescriptionShare'))->toBe(33.33);
});

it('shows a zero share when there are no prescriptions at all', function () {
    Medication::factory()->create();

    $response = $this->actingAsLoggedInUser()->getJson(route('api.medications.index'));

    expect($response->json('data.0.prescriptionShare'))->toBe(0);
});

it('counts each medication once in the page details however often it was prescribed', function () {
    $amoxicillin = Medication::factory()->create(['name' => 'Amoxicillin']);
    Medication::factory()->create(['name' => 'Gabapentin']);
    Prescription::factory()->for($amoxicillin)->count(5)->create();

    $response = $this->actingAsLoggedInUser()->getJson(route('api.medications.index', ['per_page' => 10]));

    $response->assertJson(['total' => 2, 'last_page' => 1]);
    expect($response->json('data.*.name'))->toBe(['Amoxicillin', 'Gabapentin']);
});

it('sorts the medications by name in the chosen direction', function () {
    Medication::factory()->create(['name' => 'Gabapentin']);
    Medication::factory()->create(['name' => 'Amoxicillin']);
    Medication::factory()->create(['name' => 'Metformin Hydrochloride']);

    $response = $this->actingAsLoggedInUser()->getJson(route('api.medications.index', ['sort' => 'name', 'direction' => 'desc']));

    expect($response->json('data.*.name'))->toBe(['Metformin Hydrochloride', 'Gabapentin', 'Amoxicillin']);
});

it('sorts the medications by their share of prescriptions, breaking ties by name', function (string $direction, array $expectedNames) {
    $gabapentin = Medication::factory()->create(['name' => 'Gabapentin']);
    $amoxicillin = Medication::factory()->create(['name' => 'Amoxicillin']);
    $metformin = Medication::factory()->create(['name' => 'Metformin Hydrochloride']);
    Medication::factory()->create(['name' => 'Zolpidem Tartrate']);
    Prescription::factory()->for($gabapentin)->count(3)->create();
    Prescription::factory()->for($amoxicillin)->create();
    Prescription::factory()->for($metformin)->create();

    $response = $this->actingAsLoggedInUser()->getJson(route('api.medications.index', ['sort' => 'prescriptionShare', 'direction' => $direction]));

    expect($response->json('data.*.name'))->toBe($expectedNames);
})->with([
    'ascending' => ['asc', ['Zolpidem Tartrate', 'Amoxicillin', 'Metformin Hydrochloride', 'Gabapentin']],
    'descending' => ['desc', ['Gabapentin', 'Amoxicillin', 'Metformin Hydrochloride', 'Zolpidem Tartrate']],
]);

it('sorts by name ascending when the sort column or direction is unknown', function () {
    Medication::factory()->create(['name' => 'Gabapentin']);
    Medication::factory()->create(['name' => 'Amoxicillin']);

    $response = $this->actingAsLoggedInUser()->getJson(route('api.medications.index', ['sort' => 'medID; drop table', 'direction' => 'sideways']));

    expect($response->json('data.*.name'))->toBe(['Amoxicillin', 'Gabapentin']);
});

it('leaves out removed medications', function () {
    $keptMedication = Medication::factory()->create();
    Medication::factory()->create()->delete();

    $response = $this->actingAsLoggedInUser()->getJson(route('api.medications.index'));

    expect($response->json('data.*.id'))->toBe([$keptMedication->id]);
});

it('finds medications by name', function () {
    $amoxicillin = Medication::factory()->create(['name' => 'Amoxicillin']);
    Medication::factory()->create(['name' => 'Gabapentin']);

    $response = $this->actingAsLoggedInUser()->getJson(route('api.medications.index', ['search' => 'amoxi']));

    expect($response->json('data.*.id'))->toBe([$amoxicillin->id]);
    expect($response->json('total'))->toBe(1);
});

it('finds medications by med id', function () {
    $medication = Medication::factory()->create(['medID' => 4321]);
    Medication::factory()->create(['medID' => 9876]);

    $response = $this->actingAsLoggedInUser()->getJson(route('api.medications.index', ['search' => '432']));

    expect($response->json('data.*.id'))->toBe([$medication->id]);
});

it('returns 401 to guests adding a medication', function () {
    $response = $this->postJson(route('api.medications.store'), ['name' => 'Amoxicillin']);

    $response->assertUnauthorized();
    $this->assertDatabaseEmpty('Medication');
});

it('adds a medication', function () {
    $response = $this->actingAsLoggedInUser()->postJson(route('api.medications.store'), [
        'medID' => 4321,
        'name' => 'Amoxicillin',
    ]);

    $response->assertCreated()->assertJson(['medID' => 4321, 'name' => 'Amoxicillin']);
    $this->assertDatabaseHas('Medication', ['medID' => 4321, 'name' => 'Amoxicillin']);
});

it('assigns the med id when it is left blank', function () {
    Medication::factory()->create(['medID' => 4321]);

    $response = $this->actingAsLoggedInUser()->postJson(route('api.medications.store'), [
        'medID' => '',
        'name' => 'Amoxicillin',
    ]);

    $response->assertCreated();
    expect($response->json('medID'))->toBeInt()->toBeGreaterThan(4321);
});

it('shows the MySQL error when the med id is already taken', function () {
    config(['app.debug' => true]);
    Medication::factory()->create(['medID' => 4321]);

    $response = $this->actingAsLoggedInUser()->postJson(route('api.medications.store'), [
        'medID' => 4321,
        'name' => 'Amoxicillin',
    ]);

    $response->assertInternalServerError();
    expect($response->json('message'))->toContain("Duplicate entry '4321'");
    $this->assertDatabaseCount('Medication', 1);
});

it('shows the MySQL error when the name is left blank', function () {
    config(['app.debug' => true]);

    $response = $this->actingAsLoggedInUser()->postJson(route('api.medications.store'), [
        'medID' => 4321,
        'name' => '',
    ]);

    $response->assertInternalServerError();
    expect($response->json('message'))->toContain("Column 'name' cannot be null");
    $this->assertDatabaseEmpty('Medication');
});

it('shows the MySQL error when the name is longer than 40 characters', function () {
    config(['app.debug' => true]);

    $response = $this->actingAsLoggedInUser()->postJson(route('api.medications.store'), [
        'name' => str_repeat('a', 41),
    ]);

    $response->assertInternalServerError();
    expect($response->json('message'))->toContain("Data too long for column 'name'");
    $this->assertDatabaseEmpty('Medication');
});

it('returns 401 to guests updating a medication', function () {
    $medication = Medication::factory()->create(['name' => 'Amoxicillin']);

    $response = $this->putJson(route('api.medications.update', $medication), ['name' => 'Gabapentin']);

    $response->assertUnauthorized();
    expect($medication->fresh()->name)->toBe('Amoxicillin');
});

it('updates a medication', function () {
    $medication = Medication::factory()->create(['medID' => 4321, 'name' => 'Amoxicillin']);

    $response = $this->actingAsLoggedInUser()->putJson(route('api.medications.update', $medication), [
        'medID' => 1234,
        'name' => 'Amoxicillin and Clavulanate Potassium',
    ]);

    $response->assertOk()->assertJson(['id' => $medication->id, 'medID' => 1234, 'name' => 'Amoxicillin and Clavulanate Potassium']);
    $this->assertDatabaseHas('Medication', ['id' => $medication->id, 'medID' => 1234, 'name' => 'Amoxicillin and Clavulanate Potassium']);
});

it('keeps the med id when it is left blank', function () {
    $medication = Medication::factory()->create(['medID' => 4321, 'name' => 'Amoxicillin']);

    $response = $this->actingAsLoggedInUser()->putJson(route('api.medications.update', $medication), [
        'medID' => '',
        'name' => 'Gabapentin',
    ]);

    $response->assertOk();
    $this->assertDatabaseHas('Medication', ['id' => $medication->id, 'medID' => 4321, 'name' => 'Gabapentin']);
});

it('shows the MySQL error when updating to a med id that is already taken', function () {
    config(['app.debug' => true]);
    Medication::factory()->create(['medID' => 4321]);
    $medication = Medication::factory()->create(['medID' => 1234]);

    $response = $this->actingAsLoggedInUser()->putJson(route('api.medications.update', $medication), [
        'medID' => 4321,
        'name' => 'Amoxicillin',
    ]);

    $response->assertInternalServerError();
    expect($response->json('message'))->toContain("Duplicate entry '4321'");
    expect($medication->fresh()->medID)->toBe(1234);
});

it('returns 401 to guests removing a medication', function () {
    $medication = Medication::factory()->create();

    $response = $this->deleteJson(route('api.medications.destroy', $medication));

    $response->assertUnauthorized();
    $this->assertNotSoftDeleted($medication);
});

it('removes a medication by soft deleting it', function () {
    $medication = Medication::factory()->create();

    $response = $this->actingAsLoggedInUser()->deleteJson(route('api.medications.destroy', $medication));

    $response->assertNoContent();
    $this->assertSoftDeleted($medication);
});

it('returns 404 when removing a medication that is already removed', function () {
    $medication = Medication::factory()->create();
    $medication->delete();

    $response = $this->actingAsLoggedInUser()->deleteJson(route('api.medications.destroy', $medication));

    $response->assertNotFound();
});
