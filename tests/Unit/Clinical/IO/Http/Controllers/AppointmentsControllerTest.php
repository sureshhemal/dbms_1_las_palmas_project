<?php

use App\Clinical\Entities\Models\Appointment;
use App\Patient\Entities\Models\Patient;
use App\Staff\Entities\Models\Nurse;
use App\Staff\Entities\Models\Physician;

beforeEach(function () {
    config(['app.hospital_timezone' => 'America/Denver']);
    $this->travelTo('2026-10-15 18:00:00');
});

function appointmentRequest(array $overrides = []): array
{
    return [
        'patient_id' => Patient::factory()->create()->id,
        'physician_id' => Physician::factory()->create()->id,
        'nurse_id' => Nurse::factory()->create()->id,
        'date' => '2026-10-20',
        'startTime' => '09:00',
        'endTime' => '09:30',
        ...$overrides,
    ];
}

it('returns 401 to guests', function () {
    $response = $this->getJson(route('api.appointments.index'));

    $response->assertUnauthorized();
});

it('lists the appointments on the first page, latest first', function () {
    $older = Appointment::factory()->startingAt('2025-03-10 16:00:00')->create();
    $newer = Appointment::factory()->startingAt('2026-11-02 15:00:00')->create();
    $middle = Appointment::factory()->startingAt('2026-10-01 15:00:00')->create();

    $response = $this->actingAsLoggedInUser()->getJson(route('api.appointments.index'));

    $response->assertOk()->assertJson(['current_page' => 1, 'per_page' => 20, 'total' => 3]);
    expect($response->json('data.*.id'))->toBe([$newer->id, $middle->id, $older->id]);
});

it('includes the patient, physician and nurse of each appointment', function () {
    $appointment = Appointment::factory()->create();
    $appointment->physician->delete();

    $response = $this->actingAsLoggedInUser()->getJson(route('api.appointments.index'));

    expect($response->json('data.0.patient'))->toMatchArray(['id' => $appointment->patient_id, 'name' => $appointment->patient->name]);
    expect($response->json('data.0.physician.name'))->toBe($appointment->physician->name);
    expect($response->json('data.0.physician.deleted_at'))->not->toBeNull();
    expect($response->json('data.0.nurse'))->toMatchArray(['id' => $appointment->nurse_id, 'name' => $appointment->nurse->name]);
});

it('finds appointments by patient name or patient id', function (string $searchBy) {
    $patient = Patient::factory()->create(['name' => 'Jordan Sullivan']);
    $match = Appointment::factory()->for($patient)->create();
    Appointment::factory()->create();

    $search = $searchBy === 'name' ? 'Sullivan' : (string) $patient->patientID;
    $response = $this->actingAsLoggedInUser()->getJson(route('api.appointments.index', ['search' => $search]));

    expect($response->json('data.*.id'))->toBe([$match->id]);
})->with(['name', 'patient id']);

it('filters appointments by status', function (array $statuses, array $expected) {
    $appointments = [
        'completed' => Appointment::factory()->startingAt('2026-10-15 16:00:00', minutes: 30)->create(),
        'in_progress' => Appointment::factory()->startingAt('2026-10-15 17:45:00', minutes: 30)->create(),
        'upcoming' => Appointment::factory()->startingAt('2026-10-15 18:30:00', minutes: 30)->create(),
    ];

    $response = $this->actingAsLoggedInUser()->getJson(route('api.appointments.index', ['statuses' => $statuses]));

    expect($response->json('data.*.id'))->toEqualCanonicalizing(collect($expected)->map(fn (string $key): string => $appointments[$key]->id)->all());
})->with([
    'upcoming' => [['upcoming'], ['upcoming']],
    'in progress' => [['in_progress'], ['in_progress']],
    'completed' => [['completed'], ['completed']],
    'upcoming and completed' => [['upcoming', 'completed'], ['upcoming', 'completed']],
]);

it('filters appointments by the chosen physicians', function () {
    $houseMd = Physician::factory()->create();
    $wilson = Physician::factory()->create();
    $withHouse = Appointment::factory()->for($houseMd)->create();
    $withWilson = Appointment::factory()->for($wilson)->create();
    Appointment::factory()->create();

    $response = $this->actingAsLoggedInUser()->getJson(route('api.appointments.index', ['physician_ids' => [$houseMd->id, $wilson->id]]));

    expect($response->json('data.*.id'))->toEqualCanonicalizing([$withHouse->id, $withWilson->id]);
});

it('ignores physician ids that are not ulids', function () {
    Appointment::factory()->count(2)->create();

    $response = $this->actingAsLoggedInUser()->getJson(route('api.appointments.index', ['physician_ids' => ['not-a-ulid']]));

    expect($response->json('total'))->toBe(2);
});

it('filters appointments by year in hospital time', function () {
    $newYearsEveInDenver = Appointment::factory()->startingAt('2026-01-01 05:00:00')->create();
    $newYearsDayInDenver = Appointment::factory()->startingAt('2026-01-01 08:00:00')->create();

    $response = $this->actingAsLoggedInUser()->getJson(route('api.appointments.index', ['years' => [2025]]));

    expect($response->json('data.*.id'))->toBe([$newYearsEveInDenver->id]);
    expect($this->actingAsLoggedInUser()->getJson(route('api.appointments.index', ['years' => [2026]]))->json('data.*.id'))
        ->toBe([$newYearsDayInDenver->id]);
});

it('lists the years that have appointments in hospital time, latest first', function () {
    Appointment::factory()->startingAt('2023-06-01 16:00:00')->create();
    Appointment::factory()->startingAt('2026-01-01 05:00:00')->create();
    Appointment::factory()->startingAt('2026-10-20 15:00:00')->create();

    $response = $this->actingAsLoggedInUser()->getJson(route('api.appointments.years'));

    $response->assertOk()->assertExactJson([2026, 2025, 2024, 2023]);
});

it('returns 401 to guests adding an appointment', function () {
    $response = $this->postJson(route('api.appointments.store'), appointmentRequest());

    $response->assertUnauthorized();
    expect(Appointment::query()->count())->toBe(0);
});

it('adds an appointment, storing hospital time as UTC', function () {
    $request = appointmentRequest();

    $response = $this->actingAsLoggedInUser()->postJson(route('api.appointments.store'), $request);

    $response->assertCreated();
    expect($response->json('appID'))->toBeInt();
    expect($response->json('startDateTime'))->toStartWith('2026-10-20T15:00:00');
    expect($response->json('endDateTime'))->toStartWith('2026-10-20T15:30:00');
    expect($response->json('patient.id'))->toBe($request['patient_id']);
    $this->assertDatabaseHas('Appointment', [
        'patient_id' => $request['patient_id'],
        'physician_id' => $request['physician_id'],
        'nurse_id' => $request['nurse_id'],
        'startDateTime' => '2026-10-20 15:00:00',
        'endDateTime' => '2026-10-20 15:30:00',
    ]);
});

it('shows the MySQL error when the end time is left out', function () {
    config(['app.debug' => true]);

    $response = $this->actingAsLoggedInUser()->postJson(route('api.appointments.store'), appointmentRequest(['endTime' => '']));

    $response->assertInternalServerError();
    expect($response->json('message'))->toContain("Column 'endDateTime' cannot be null");
    expect(Appointment::query()->count())->toBe(0);
});

it('shows the MySQL error when the appointment ends before it starts', function () {
    config(['app.debug' => true]);

    $response = $this->actingAsLoggedInUser()->postJson(route('api.appointments.store'), appointmentRequest(['startTime' => '10:00', 'endTime' => '09:30']));

    $response->assertInternalServerError();
    expect($response->json('message'))->toContain('appointment_date_times_check');
    expect(Appointment::query()->count())->toBe(0);
});

it('returns 404 when the patient, physician or nurse is not active', function (string $field) {
    $request = appointmentRequest();
    match ($field) {
        'patient_id' => Patient::query()->find($request['patient_id'])->delete(),
        'physician_id' => Physician::query()->find($request['physician_id'])->delete(),
        'nurse_id' => Nurse::query()->find($request['nurse_id'])->delete(),
    };

    $response = $this->actingAsLoggedInUser()->postJson(route('api.appointments.store'), $request);

    $response->assertNotFound();
    expect(Appointment::query()->count())->toBe(0);
})->with(['patient_id', 'physician_id', 'nurse_id']);

it('returns 401 to guests updating an appointment', function () {
    $appointment = Appointment::factory()->create();

    $response = $this->putJson(route('api.appointments.update', $appointment), appointmentRequest());

    $response->assertUnauthorized();
});

it('extends an appointment by editing its end time', function () {
    $appointment = Appointment::factory()->startingAt('2026-10-20 15:00:00', minutes: 30)->create();

    $response = $this->actingAsLoggedInUser()->putJson(route('api.appointments.update', $appointment), [
        'patient_id' => $appointment->patient_id,
        'physician_id' => $appointment->physician_id,
        'nurse_id' => $appointment->nurse_id,
        'date' => '2026-10-20',
        'startTime' => '09:00',
        'endTime' => '10:15',
    ]);

    $response->assertOk();
    expect($response->json('endDateTime'))->toStartWith('2026-10-20T16:15:00');
    expect($appointment->fresh()->endDateTime->toDateTimeString())->toBe('2026-10-20 16:15:00');
    expect($appointment->fresh()->appID)->toBe($appointment->appID);
});

it('moves an appointment to another day, physician and nurse', function () {
    $appointment = Appointment::factory()->create();
    $physician = Physician::factory()->create();
    $nurse = Nurse::factory()->create();

    $response = $this->actingAsLoggedInUser()->putJson(route('api.appointments.update', $appointment), [
        'patient_id' => $appointment->patient_id,
        'physician_id' => $physician->id,
        'nurse_id' => $nurse->id,
        'date' => '2026-11-03',
        'startTime' => '14:00',
        'endTime' => '14:45',
    ]);

    $response->assertOk();
    $this->assertDatabaseHas('Appointment', [
        'id' => $appointment->id,
        'physician_id' => $physician->id,
        'nurse_id' => $nurse->id,
        'startDateTime' => '2026-11-03 21:00:00',
        'endDateTime' => '2026-11-03 21:45:00',
    ]);
});

it('returns 401 to guests cancelling an appointment', function () {
    $appointment = Appointment::factory()->startingAt('2026-10-20 15:00:00')->create();

    $response = $this->deleteJson(route('api.appointments.destroy', $appointment));

    $response->assertUnauthorized();
    expect($appointment->fresh()->deleted_at)->toBeNull();
});

it('cancels an upcoming appointment by soft deleting it', function () {
    $appointment = Appointment::factory()->startingAt('2026-10-20 15:00:00')->create();

    $response = $this->actingAsLoggedInUser()->deleteJson(route('api.appointments.destroy', $appointment));

    $response->assertNoContent();
    expect(Appointment::query()->find($appointment->id))->toBeNull();
    $this->assertSoftDeleted('Appointment', ['id' => $appointment->id]);
});

it('does not cancel an appointment that has already started', function (string $startsAt) {
    $appointment = Appointment::factory()->startingAt($startsAt, minutes: 30)->create();

    $response = $this->actingAsLoggedInUser()->deleteJson(route('api.appointments.destroy', $appointment));

    $response->assertUnprocessable()->assertJsonValidationErrors('appointment');
    expect($appointment->fresh()->deleted_at)->toBeNull();
})->with([
    'in progress' => ['2026-10-15 17:45:00'],
    'completed' => ['2026-10-14 16:00:00'],
]);

it('returns 404 when cancelling an appointment that is already cancelled', function () {
    $appointment = Appointment::factory()->startingAt('2026-10-20 15:00:00')->create();
    $appointment->delete();

    $response = $this->actingAsLoggedInUser()->deleteJson(route('api.appointments.destroy', $appointment));

    $response->assertNotFound();
});

it('leaves cancelled appointments out of the list', function () {
    $kept = Appointment::factory()->create();
    Appointment::factory()->create()->delete();

    $response = $this->actingAsLoggedInUser()->getJson(route('api.appointments.index'));

    expect($response->json('data.*.id'))->toBe([$kept->id]);
});
