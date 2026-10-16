<?php

use App\Clinical\Entities\Models\Medication;
use App\Clinical\Entities\Models\Prescription;
use App\Patient\Entities\Models\Patient;
use App\Staff\Entities\Models\Physician;
use App\Staff\Entities\PhysicianPosition;

function prescriptionRequest(array $overrides = []): array
{
    return [
        'patient_id' => Patient::factory()->create(['created_at' => '2020-01-01 09:00:00'])->id,
        'medication_id' => Medication::factory()->create()->id,
        'physician_id' => Physician::factory()->withPosition(PhysicianPosition::Senior)->create()->id,
        'prescribedDate' => '2026-10-01',
        'dose' => '500 mg twice daily',
        ...$overrides,
    ];
}

it('returns 401 to guests', function () {
    $response = $this->getJson(route('api.prescriptions.index'));

    $response->assertUnauthorized();
});

it('lists the prescriptions on the first page, most recently prescribed first', function () {
    $older = Prescription::factory()->prescribedOn('2026-01-15')->create();
    $newer = Prescription::factory()->prescribedOn('2026-09-30')->create();
    $middle = Prescription::factory()->prescribedOn('2026-05-01')->create();

    $response = $this->actingAsLoggedInUser()->getJson(route('api.prescriptions.index'));

    $response->assertOk()->assertJson(['current_page' => 1, 'per_page' => 20, 'total' => 3]);
    expect($response->json('data.*.id'))->toBe([$newer->id, $middle->id, $older->id]);
});

it('returns the requested page of prescriptions', function () {
    Prescription::factory()->count(25)->create();

    $response = $this->actingAsLoggedInUser()->getJson(route('api.prescriptions.index', ['page' => 2, 'per_page' => 10]));

    $response->assertOk()->assertJsonCount(10, 'data')->assertJson(['current_page' => 2, 'total' => 25, 'last_page' => 3]);
});

it('includes the patient, medication and prescribing physician', function () {
    $patient = Patient::factory()->create(['name' => 'Jordan Sullivan', 'patientID' => 321]);
    $medication = Medication::factory()->create(['name' => 'Amoxicillin', 'medID' => 654]);
    $physician = Physician::factory()->withPosition(PhysicianPosition::Senior)->create(['name' => 'Perry Cox', 'physicianID' => 987]);
    Prescription::factory()->for($patient)->for($medication)->for($physician)->prescribedOn('2026-10-01')->create(['dose' => '500 mg twice daily']);

    $response = $this->actingAsLoggedInUser()->getJson(route('api.prescriptions.index'));

    expect($response->json('data.0'))->toMatchArray(['prescribedDate' => '2026-10-01', 'dose' => '500 mg twice daily']);
    expect($response->json('data.0.patient'))->toMatchArray(['id' => $patient->id, 'patientID' => 321, 'name' => 'Jordan Sullivan', 'deleted_at' => null]);
    expect($response->json('data.0.medication'))->toMatchArray(['id' => $medication->id, 'medID' => 654, 'name' => 'Amoxicillin', 'deleted_at' => null]);
    expect($response->json('data.0.physician'))->toMatchArray(['id' => $physician->id, 'physicianID' => 987, 'name' => 'Perry Cox', 'deleted_at' => null]);
});

it('keeps showing prescriptions of offboarded physicians, removed patients and removed medications', function () {
    $prescription = Prescription::factory()->create();
    $prescription->physician->delete();
    $prescription->patient->delete();
    $prescription->medication->delete();

    $response = $this->actingAsLoggedInUser()->getJson(route('api.prescriptions.index'));

    expect($response->json('data.0.id'))->toBe($prescription->id);
    expect($response->json('data.0.physician.deleted_at'))->not->toBeNull();
    expect($response->json('data.0.patient.deleted_at'))->not->toBeNull();
    expect($response->json('data.0.medication.deleted_at'))->not->toBeNull();
});

it('finds prescriptions by patient, patient id, medication or physician', function (string $search) {
    $prescription = Prescription::factory()
        ->for(Patient::factory()->state(['name' => 'Jordan Sullivan', 'patientID' => 321]))
        ->for(Medication::factory()->state(['name' => 'Amoxicillin']))
        ->for(Physician::factory()->withPosition(PhysicianPosition::Senior)->state(['name' => 'Perry Cox']))
        ->create();
    Prescription::factory()
        ->for(Patient::factory()->state(['name' => 'Carla Espinosa', 'patientID' => 654]))
        ->for(Medication::factory()->state(['name' => 'Gabapentin']))
        ->for(Physician::factory()->withPosition(PhysicianPosition::Surgeon)->state(['name' => 'Chris Turk']))
        ->create();

    $response = $this->actingAsLoggedInUser()->getJson(route('api.prescriptions.index', ['search' => $search]));

    expect($response->json('data.*.id'))->toBe([$prescription->id]);
})->with([
    'patient name' => ['jordan'],
    'patient id' => ['321'],
    'medication' => ['amoxi'],
    'physician' => ['perry'],
]);

it('filters prescriptions by the chosen years', function () {
    $from2024 = Prescription::factory()->prescribedOn('2024-06-01')->create();
    $from2026 = Prescription::factory()->prescribedOn('2026-02-01')->create();
    Prescription::factory()->prescribedOn('2025-06-01')->create();

    $response = $this->actingAsLoggedInUser()->getJson(route('api.prescriptions.index', ['years' => [2024, 2026]]));

    expect($response->json('data.*.id'))->toBe([$from2026->id, $from2024->id]);
});

it('lists the years that have prescriptions, newest first', function () {
    Prescription::factory()->prescribedOn('2024-06-01')->create();
    Prescription::factory()->prescribedOn('2026-02-01')->create();
    Prescription::factory()->prescribedOn('2026-03-01')->create();

    $response = $this->actingAsLoggedInUser()->getJson(route('api.prescriptions.years'));

    $response->assertOk();
    expect($response->json())->toBe([2026, 2024]);
});

it('returns 401 to guests adding a prescription', function () {
    $response = $this->postJson(route('api.prescriptions.store'), prescriptionRequest());

    $response->assertUnauthorized();
    $this->assertDatabaseEmpty('Prescribes');
});

it('adds a prescription', function () {
    $this->travelTo('2026-10-15 12:00:00');
    $request = prescriptionRequest();

    $response = $this->actingAsLoggedInUser()->postJson(route('api.prescriptions.store'), $request);

    $response->assertCreated()->assertJson(['prescribedDate' => '2026-10-01', 'dose' => '500 mg twice daily']);
    expect($response->json('patient.id'))->toBe($request['patient_id']);
    $this->assertDatabaseHas('Prescribes', $request);
});

it('shows the MySQL error when the dose is left blank', function (string $dose) {
    config(['app.debug' => true]);
    $this->travelTo('2026-10-15 12:00:00');

    $response = $this->actingAsLoggedInUser()->postJson(route('api.prescriptions.store'), prescriptionRequest(['dose' => $dose]));

    $response->assertInternalServerError();
    expect($response->json('message'))->toContain("Column 'dose' cannot be null");
    $this->assertDatabaseEmpty('Prescribes');
})->with([
    'left blank' => [''],
    'only spaces' => ['   '],
]);

it('allows prescribing on the hospital today', function () {
    config(['app.hospital_timezone' => 'Asia/Colombo']);
    $this->travelTo('2026-10-15 20:00:00');

    $response = $this->actingAsLoggedInUser()->postJson(route('api.prescriptions.store'), prescriptionRequest(['prescribedDate' => '2026-10-16']));

    $response->assertCreated();
});

it('rejects a prescription dated in the future', function () {
    $this->travelTo('2026-10-15 12:00:00');

    $response = $this->actingAsLoggedInUser()->postJson(route('api.prescriptions.store'), prescriptionRequest(['prescribedDate' => '2026-10-16']));

    $response->assertUnprocessable()->assertJsonValidationErrors([
        'prescribedDate' => 'A prescription cannot be dated in the future.',
    ]);
    $this->assertDatabaseEmpty('Prescribes');
});

it('rejects a prescription dated before the patient registered', function () {
    $this->travelTo('2024-03-01 10:00:00');
    $patient = Patient::factory()->create(['name' => 'Jordan Sullivan']);
    $this->travelTo('2026-10-15 12:00:00');

    $response = $this->actingAsLoggedInUser()->postJson(route('api.prescriptions.store'), prescriptionRequest([
        'patient_id' => $patient->id,
        'prescribedDate' => '2024-02-29',
    ]));

    $response->assertUnprocessable()->assertJsonValidationErrors([
        'prescribedDate' => 'Jordan Sullivan registered on 2024-03-01, so a prescription cannot be dated earlier.',
    ]);
    $this->assertDatabaseEmpty('Prescribes');
});

it('allows a prescription on the day the patient registered', function () {
    $this->travelTo('2024-03-01 10:00:00');
    $patient = Patient::factory()->create();
    $this->travelTo('2026-10-15 12:00:00');

    $response = $this->actingAsLoggedInUser()->postJson(route('api.prescriptions.store'), prescriptionRequest([
        'patient_id' => $patient->id,
        'prescribedDate' => '2024-03-01',
    ]));

    $response->assertCreated();
});

it('uses the hospital day the patient registered on', function () {
    config(['app.hospital_timezone' => 'Asia/Colombo']);
    $this->travelTo('2024-03-01 20:00:00');
    $patient = Patient::factory()->create(['name' => 'Jordan Sullivan']);
    $this->travelTo('2026-10-15 12:00:00');

    $response = $this->actingAsLoggedInUser()->postJson(route('api.prescriptions.store'), prescriptionRequest([
        'patient_id' => $patient->id,
        'prescribedDate' => '2024-03-01',
    ]));

    $response->assertUnprocessable()->assertJsonValidationErrors([
        'prescribedDate' => 'Jordan Sullivan registered on 2024-03-02, so a prescription cannot be dated earlier.',
    ]);
});

it('rejects an intern as the prescriber', function () {
    $intern = Physician::factory()->withPosition(PhysicianPosition::Intern)->create(['name' => 'Elliot Reid']);

    $response = $this->actingAsLoggedInUser()->postJson(route('api.prescriptions.store'), prescriptionRequest(['physician_id' => $intern->id]));

    $response->assertUnprocessable()->assertJsonValidationErrors([
        'physician_id' => 'Elliot Reid is an intern and cannot prescribe.',
    ]);
});

it('rejects an offboarded physician as the prescriber', function () {
    $physician = Physician::factory()->withPosition(PhysicianPosition::Senior)->create(['name' => 'Perry Cox']);
    $physician->delete();

    $response = $this->actingAsLoggedInUser()->postJson(route('api.prescriptions.store'), prescriptionRequest(['physician_id' => $physician->id]));

    $response->assertUnprocessable()->assertJsonValidationErrors([
        'physician_id' => 'Perry Cox has been offboarded and cannot prescribe.',
    ]);
});

it('rejects an inactive patient', function () {
    $patient = Patient::factory()->create(['name' => 'Jordan Sullivan']);
    $patient->delete();

    $response = $this->actingAsLoggedInUser()->postJson(route('api.prescriptions.store'), prescriptionRequest(['patient_id' => $patient->id]));

    $response->assertUnprocessable()->assertJsonValidationErrors([
        'patient_id' => 'Jordan Sullivan is inactive and cannot be prescribed to.',
    ]);
});

it('rejects a removed medication', function () {
    $medication = Medication::factory()->create(['name' => 'Amoxicillin']);
    $medication->delete();

    $response = $this->actingAsLoggedInUser()->postJson(route('api.prescriptions.store'), prescriptionRequest(['medication_id' => $medication->id]));

    $response->assertUnprocessable()->assertJsonValidationErrors([
        'medication_id' => 'Amoxicillin has been removed and cannot be prescribed.',
    ]);
});

it('shows the MySQL error when the same prescription is written twice on the same day', function () {
    config(['app.debug' => true]);
    $this->travelTo('2026-10-15 12:00:00');
    $request = prescriptionRequest();
    $this->actingAsLoggedInUser()->postJson(route('api.prescriptions.store'), $request)->assertCreated();

    $response = $this->actingAsLoggedInUser()->postJson(route('api.prescriptions.store'), $request);

    $response->assertInternalServerError();
    expect($response->json('message'))->toContain('Duplicate entry');
    $this->assertDatabaseCount('Prescribes', 1);
});

it('shows the MySQL error when the prescribed date is left blank', function () {
    config(['app.debug' => true]);

    $response = $this->actingAsLoggedInUser()->postJson(route('api.prescriptions.store'), prescriptionRequest(['prescribedDate' => '']));

    $response->assertInternalServerError();
    expect($response->json('message'))->toContain("Column 'prescribedDate' cannot be null");
});

it('has no way to change or remove a prescription', function (string $method) {
    $prescription = Prescription::factory()->create();

    $response = $this->actingAsLoggedInUser()->json($method, "/api/prescriptions/{$prescription->id}", ['dose' => 'changed']);

    $response->assertNotFound();
    $this->assertNotSoftDeleted($prescription);
    expect($prescription->fresh()->dose)->not->toBe('changed');
})->with(['PUT', 'PATCH', 'DELETE']);
