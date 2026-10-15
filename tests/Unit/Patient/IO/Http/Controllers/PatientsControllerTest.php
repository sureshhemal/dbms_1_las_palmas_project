<?php

use App\Patient\Entities\Models\Patient;
use App\Staff\Entities\Models\Physician;
use App\Staff\Entities\PhysicianPosition;

it('returns 401 to guests', function () {
    $response = $this->getJson(route('api.patients.index'));

    $response->assertUnauthorized();
});

it('lists the patients on the first page, newest first', function () {
    $this->travel(-1)->days();
    $olderPatient = Patient::factory()->create();
    $this->travelBack();
    $newerPatient = Patient::factory()->create();

    $response = $this->actingAsLoggedInUser()->getJson(route('api.patients.index'));

    $response->assertOk()->assertJson(['current_page' => 1, 'per_page' => 20, 'total' => 2]);
    expect($response->json('data.*.id'))->toBe([$newerPatient->id, $olderPatient->id]);
});

it('returns the requested page of patients', function () {
    Patient::factory()->count(25)->create();

    $response = $this->actingAsLoggedInUser()->getJson(route('api.patients.index', ['page' => 2, 'per_page' => 10]));

    $response->assertOk()->assertJsonCount(10, 'data')->assertJson([
        'current_page' => 2,
        'per_page' => 10,
        'from' => 11,
        'to' => 20,
        'total' => 25,
        'last_page' => 3,
    ]);
});

it('includes the age of each patient', function () {
    $this->travelTo('2026-10-15 12:00:00');
    Patient::factory()->create(['dob' => '1975-04-12']);

    $response = $this->actingAsLoggedInUser()->getJson(route('api.patients.index'));

    expect($response->json('data.0.age'))->toBe(51);
});

it('includes the primary physician of each patient', function () {
    $physician = Physician::factory()->create(['name' => 'Perry Cox', 'physicianID' => 432]);
    Patient::factory()->withPrimaryPhysician($physician)->create();

    $response = $this->actingAsLoggedInUser()->getJson(route('api.patients.index'));

    expect($response->json('data.0.primary_physician'))->toBe([
        'id' => $physician->id,
        'physicianID' => 432,
        'name' => 'Perry Cox',
        'deleted_at' => null,
    ]);
});

it('still shows a primary physician who has been offboarded', function () {
    $physician = Physician::factory()->create(['name' => 'Perry Cox']);
    Patient::factory()->withPrimaryPhysician($physician)->create();
    $physician->delete();

    $response = $this->actingAsLoggedInUser()->getJson(route('api.patients.index'));

    expect($response->json('data.0.primary_physician.name'))->toBe('Perry Cox');
    expect($response->json('data.0.primary_physician.deleted_at'))->not->toBeNull();
});

it('leaves out removed patients', function () {
    $keptPatient = Patient::factory()->create();
    Patient::factory()->create()->delete();

    $response = $this->actingAsLoggedInUser()->getJson(route('api.patients.index'));

    expect($response->json('data.*.id'))->toBe([$keptPatient->id]);
});

it('finds patients by name, patient id, phone or insurance number', function (array $attributes, string $search) {
    $patient = Patient::factory()->create($attributes);
    Patient::factory()->create(['name' => 'Someone Else', 'patientID' => 98765, 'phone' => '(555) 000-0000', 'insuranceNumber' => 11111111]);

    $response = $this->actingAsLoggedInUser()->getJson(route('api.patients.index', ['search' => $search]));

    expect($response->json('data.*.id'))->toBe([$patient->id]);
})->with([
    'name' => [['name' => 'Jordan Sullivan'], 'jordan'],
    'patient id' => [['patientID' => 12345], '1234'],
    'phone' => [['phone' => '(555) 867-5309'], '867-5309'],
    'insurance number' => [['insuranceNumber' => 44556677], '4455667'],
]);

it('filters patients by their primary physician', function () {
    $perryCox = Physician::factory()->create();
    $patientOfPerry = Patient::factory()->withPrimaryPhysician($perryCox)->create();
    Patient::factory()->withPrimaryPhysician(Physician::factory()->create())->create();
    Patient::factory()->create(['primary_physician_id' => null]);

    $response = $this->actingAsLoggedInUser()->getJson(route('api.patients.index', ['primary_physician_ids' => [$perryCox->id]]));

    expect($response->json('data.*.id'))->toBe([$patientOfPerry->id]);
});

it('returns 401 to guests adding a patient', function () {
    $response = $this->postJson(route('api.patients.store'), ['name' => 'Jordan Sullivan']);

    $response->assertUnauthorized();
    $this->assertDatabaseEmpty('Patient');
});

it('adds a patient', function () {
    $physician = Physician::factory()->withPosition(PhysicianPosition::Senior)->create(['name' => 'Perry Cox']);

    $response = $this->actingAsLoggedInUser()->postJson(route('api.patients.store'), [
        'patientID' => 12345,
        'name' => 'Jordan Sullivan',
        'ssn' => '012345678',
        'address' => '12 Sacred Heart Way, San Di Frangeles',
        'dob' => '1975-04-12',
        'phone' => '(555) 867-5309',
        'insuranceNumber' => 44556677,
        'primary_physician_id' => $physician->id,
    ]);

    $response->assertCreated()->assertJson([
        'patientID' => 12345,
        'name' => 'Jordan Sullivan',
        'dob' => '1975-04-12',
        'primary_physician' => ['name' => 'Perry Cox'],
    ]);
    $this->assertDatabaseHas('Patient', [
        'patientID' => 12345,
        'name' => 'Jordan Sullivan',
        'ssn' => '012345678',
        'address' => '12 Sacred Heart Way, San Di Frangeles',
        'dob' => '1975-04-12',
        'phone' => '(555) 867-5309',
        'insuranceNumber' => 44556677,
        'primary_physician_id' => $physician->id,
    ]);
});

it('adds a patient with only a name and assigns the patient id', function () {
    Patient::factory()->create(['patientID' => 12345]);

    $response = $this->actingAsLoggedInUser()->postJson(route('api.patients.store'), [
        'patientID' => '',
        'name' => 'Jordan Sullivan',
        'ssn' => '',
        'address' => '',
        'dob' => '',
        'phone' => '',
        'insuranceNumber' => '',
        'primary_physician_id' => '',
    ]);

    $response->assertCreated();
    expect($response->json('patientID'))->toBeInt()->toBeGreaterThan(12345);
    $this->assertDatabaseHas('Patient', [
        'name' => 'Jordan Sullivan',
        'ssn' => null,
        'address' => null,
        'dob' => null,
        'phone' => null,
        'insuranceNumber' => null,
        'primary_physician_id' => null,
    ]);
});

it('lets family members share an insurance number', function () {
    Patient::factory()->create(['insuranceNumber' => 44556677]);

    $response = $this->actingAsLoggedInUser()->postJson(route('api.patients.store'), [
        'name' => 'Jordan Sullivan',
        'insuranceNumber' => 44556677,
    ]);

    $response->assertCreated();
    $this->assertDatabaseCount('Patient', 2);
});

it('rejects an intern as the primary physician', function () {
    $intern = Physician::factory()->withPosition(PhysicianPosition::Intern)->create(['name' => 'Elliot Reid']);

    $response = $this->actingAsLoggedInUser()->postJson(route('api.patients.store'), [
        'name' => 'Jordan Sullivan',
        'primary_physician_id' => $intern->id,
    ]);

    $response->assertUnprocessable()->assertJsonValidationErrors([
        'primary_physician_id' => 'Elliot Reid is an intern and cannot be a primary physician.',
    ]);
    $this->assertDatabaseEmpty('Patient');
});

it('rejects an offboarded physician as a new primary physician', function () {
    $physician = Physician::factory()->withPosition(PhysicianPosition::Senior)->create(['name' => 'Perry Cox']);
    $physician->delete();

    $response = $this->actingAsLoggedInUser()->postJson(route('api.patients.store'), [
        'name' => 'Jordan Sullivan',
        'primary_physician_id' => $physician->id,
    ]);

    $response->assertUnprocessable()->assertJsonValidationErrors([
        'primary_physician_id' => 'Perry Cox has been offboarded and cannot become a primary physician.',
    ]);
    $this->assertDatabaseEmpty('Patient');
});

it('keeps an offboarded primary physician when other details are edited', function () {
    $physician = Physician::factory()->withPosition(PhysicianPosition::Senior)->create();
    $patient = Patient::factory()->withPrimaryPhysician($physician)->create();
    $physician->delete();

    $response = $this->actingAsLoggedInUser()->putJson(route('api.patients.update', $patient), [
        'name' => 'Jordan Sullivan',
        'primary_physician_id' => $physician->id,
    ]);

    $response->assertOk();
    $this->assertDatabaseHas('Patient', ['id' => $patient->id, 'name' => 'Jordan Sullivan', 'primary_physician_id' => $physician->id]);
});

it('shows the MySQL error when the patient id is already taken', function () {
    config(['app.debug' => true]);
    Patient::factory()->create(['patientID' => 12345]);

    $response = $this->actingAsLoggedInUser()->postJson(route('api.patients.store'), [
        'patientID' => 12345,
        'name' => 'Jordan Sullivan',
    ]);

    $response->assertInternalServerError();
    expect($response->json('message'))->toContain("Duplicate entry '12345'");
    $this->assertDatabaseCount('Patient', 1);
});

it('shows the MySQL error when the name is left blank', function () {
    config(['app.debug' => true]);

    $response = $this->actingAsLoggedInUser()->postJson(route('api.patients.store'), [
        'name' => '',
    ]);

    $response->assertInternalServerError();
    expect($response->json('message'))->toContain("Column 'name' cannot be null");
    $this->assertDatabaseEmpty('Patient');
});

it('shows the MySQL error when the primary physician does not exist', function () {
    config(['app.debug' => true]);

    $response = $this->actingAsLoggedInUser()->postJson(route('api.patients.store'), [
        'name' => 'Jordan Sullivan',
        'primary_physician_id' => (string) str()->ulid(),
    ]);

    $response->assertInternalServerError();
    expect($response->json('message'))->toContain('foreign key constraint fails');
    $this->assertDatabaseEmpty('Patient');
});

it('returns 401 to guests updating a patient', function () {
    $patient = Patient::factory()->create(['name' => 'Jordan Sullivan']);

    $response = $this->putJson(route('api.patients.update', $patient), ['name' => 'Carla Espinosa']);

    $response->assertUnauthorized();
    expect($patient->fresh()->name)->toBe('Jordan Sullivan');
});

it('updates a patient', function () {
    $patient = Patient::factory()->create(['patientID' => 12345]);
    $physician = Physician::factory()->withPosition(PhysicianPosition::Psychiatrist)->create();

    $response = $this->actingAsLoggedInUser()->putJson(route('api.patients.update', $patient), [
        'patientID' => 54321,
        'name' => 'Jordan Sullivan',
        'ssn' => '987654321',
        'address' => '1 Main Street',
        'dob' => '1980-01-31',
        'phone' => '555-0100',
        'insuranceNumber' => 99887766,
        'primary_physician_id' => $physician->id,
    ]);

    $response->assertOk()->assertJson(['id' => $patient->id, 'patientID' => 54321, 'name' => 'Jordan Sullivan', 'dob' => '1980-01-31']);
    $this->assertDatabaseHas('Patient', [
        'id' => $patient->id,
        'patientID' => 54321,
        'ssn' => '987654321',
        'address' => '1 Main Street',
        'dob' => '1980-01-31',
        'phone' => '555-0100',
        'insuranceNumber' => 99887766,
        'primary_physician_id' => $physician->id,
    ]);
});

it('keeps the patient id and clears the optional details when they are left blank', function () {
    $patient = Patient::factory()->withPrimaryPhysician(Physician::factory()->create())->create(['patientID' => 12345]);

    $response = $this->actingAsLoggedInUser()->putJson(route('api.patients.update', $patient), [
        'patientID' => '',
        'name' => 'Jordan Sullivan',
        'ssn' => '',
        'address' => '',
        'dob' => '',
        'phone' => '',
        'insuranceNumber' => '',
        'primary_physician_id' => '',
    ]);

    $response->assertOk();
    $this->assertDatabaseHas('Patient', [
        'id' => $patient->id,
        'patientID' => 12345,
        'ssn' => null,
        'address' => null,
        'dob' => null,
        'phone' => null,
        'insuranceNumber' => null,
        'primary_physician_id' => null,
    ]);
});

it('shows the MySQL error when updating to a patient id that is already taken', function () {
    config(['app.debug' => true]);
    Patient::factory()->create(['patientID' => 12345]);
    $patient = Patient::factory()->create(['patientID' => 54321]);

    $response = $this->actingAsLoggedInUser()->putJson(route('api.patients.update', $patient), [
        'patientID' => 12345,
        'name' => 'Jordan Sullivan',
    ]);

    $response->assertInternalServerError();
    expect($response->json('message'))->toContain("Duplicate entry '12345'");
    expect($patient->fresh()->patientID)->toBe(54321);
});

it('returns 401 to guests removing a patient', function () {
    $patient = Patient::factory()->create();

    $response = $this->deleteJson(route('api.patients.destroy', $patient));

    $response->assertUnauthorized();
    $this->assertNotSoftDeleted($patient);
});

it('removes a patient by soft deleting them', function () {
    $patient = Patient::factory()->create();

    $response = $this->actingAsLoggedInUser()->deleteJson(route('api.patients.destroy', $patient));

    $response->assertNoContent();
    $this->assertSoftDeleted($patient);
});

it('returns 404 when removing a patient who is already removed', function () {
    $patient = Patient::factory()->create();
    $patient->delete();

    $response = $this->actingAsLoggedInUser()->deleteJson(route('api.patients.destroy', $patient));

    $response->assertNotFound();
});
