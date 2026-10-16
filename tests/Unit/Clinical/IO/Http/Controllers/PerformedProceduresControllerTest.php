<?php

use App\Clinical\Entities\Models\PerformedProcedure;
use App\Clinical\Entities\Models\Procedure;
use App\Clinical\Entities\Models\Stay;
use App\Hospital\Entities\Models\Room;
use App\Hospital\Entities\RoomType;
use App\Patient\Entities\Models\Patient;
use App\Staff\Entities\Models\Nurse;
use App\Staff\Entities\Models\Physician;
use App\Staff\Entities\PhysicianPosition;

beforeEach(function () {
    $this->travelTo('2026-10-15 12:00:00');
});

function registeredPatientForProcedure(array $attributes = []): Patient
{
    return Patient::factory()->create(['created_at' => '2020-01-01 09:00:00', ...$attributes]);
}

function procedureEntryRequest(array $overrides = []): array
{
    return [
        'patient_id' => registeredPatientForProcedure()->id,
        'procedure_id' => Procedure::factory()->create()->id,
        'physician_id' => Physician::factory()->withPosition(PhysicianPosition::Surgeon)->create()->id,
        'nurse_id' => Nurse::factory()->create()->id,
        'procDate' => '2026-10-10',
        'room_id' => Room::factory()->withType(RoomType::Double)->create()->id,
        'startDate' => '2026-10-10',
        'endDate' => '',
        ...$overrides,
    ];
}

it('returns 401 to guests', function () {
    $response = $this->getJson(route('api.performed-procedures.index'));

    $response->assertUnauthorized();
});

it('lists the performed procedures on the first page, latest first', function () {
    $older = PerformedProcedure::factory()->performedOn('2026-01-15')->create();
    $newer = PerformedProcedure::factory()->performedOn('2026-09-30')->create();

    $response = $this->actingAsLoggedInUser()->getJson(route('api.performed-procedures.index'));

    $response->assertOk()->assertJson(['current_page' => 1, 'per_page' => 20, 'total' => 2]);
    expect($response->json('data.*.id'))->toBe([$newer->id, $older->id]);
});

it('includes the patient, procedure, stay, physician and nurse', function () {
    $performedProcedure = PerformedProcedure::factory()->performedOn('2026-10-07')->create();
    $performedProcedure->physician->delete();

    $response = $this->actingAsLoggedInUser()->getJson(route('api.performed-procedures.index'));

    expect($response->json('data.0.procDate'))->toBe('2026-10-07');
    expect($response->json('data.0.patient'))->toMatchArray(['id' => $performedProcedure->patient_id, 'name' => $performedProcedure->patient->name]);
    expect($response->json('data.0.procedure'))->toMatchArray(['id' => $performedProcedure->procedure_id, 'name' => $performedProcedure->procedure->name]);
    expect($response->json('data.0.stay'))->toMatchArray(['id' => $performedProcedure->stay_id, 'stayID' => $performedProcedure->stay->stayID]);
    expect($response->json('data.0.physician.name'))->toBe($performedProcedure->physician->name);
    expect($response->json('data.0.physician'))->not->toHaveKey('deleted_at');
    expect($response->json('data.0.patient'))->not->toHaveKey('deleted_at');
    expect($response->json('data.0.nurse'))->toMatchArray(['id' => $performedProcedure->nurse_id, 'name' => $performedProcedure->nurse->name]);
});

it('finds performed procedures by patient name or patient id', function (string $search) {
    $match = PerformedProcedure::factory()->create();
    $match->patient->update(['name' => 'Jordan Sullivan', 'patientID' => 4321]);
    PerformedProcedure::factory()->create();

    $response = $this->actingAsLoggedInUser()->getJson(route('api.performed-procedures.index', ['search' => $search]));

    expect($response->json('data.*.id'))->toBe([$match->id]);
})->with([
    'patient name' => ['jordan'],
    'patient id' => ['4321'],
]);

it('does not search by procedure, physician or nurse names', function (string $search) {
    $performedProcedure = PerformedProcedure::factory()->create();
    $performedProcedure->procedure->update(['name' => 'Appendectomy']);
    $performedProcedure->physician->update(['name' => 'Chris Turk']);
    $performedProcedure->nurse->update(['name' => 'Carla Espinosa']);

    $response = $this->actingAsLoggedInUser()->getJson(route('api.performed-procedures.index', ['search' => $search]));

    $response->assertJsonCount(0, 'data');
})->with(['append', 'turk', 'espinosa']);

it('filters performed procedures by the chosen physicians, nurses or procedures', function (string $filter, string $column) {
    $chosen = PerformedProcedure::factory()->create();
    PerformedProcedure::factory()->create();

    $response = $this->actingAsLoggedInUser()->getJson(route('api.performed-procedures.index', [$filter => [$chosen->{$column}]]));

    expect($response->json('data.*.id'))->toBe([$chosen->id]);
})->with([
    'physicians' => ['physician_ids', 'physician_id'],
    'nurses' => ['nurse_ids', 'nurse_id'],
    'procedures' => ['procedure_ids', 'procedure_id'],
]);

it('combines the physician and nurse filters', function () {
    $both = PerformedProcedure::factory()->create();
    $samePhysicianOtherNurse = PerformedProcedure::factory()->create(['physician_id' => $both->physician_id]);

    $response = $this->actingAsLoggedInUser()->getJson(route('api.performed-procedures.index', [
        'physician_ids' => [$both->physician_id],
        'nurse_ids' => [$both->nurse_id],
    ]));

    expect($response->json('data.*.id'))->toBe([$both->id])->not->toContain($samePhysicianOtherNurse->id);
});

it('ignores filter values that are not ids', function () {
    PerformedProcedure::factory()->count(2)->create();

    $response = $this->actingAsLoggedInUser()->getJson(route('api.performed-procedures.index', ['physician_ids' => ['turk']]));

    $response->assertJsonCount(2, 'data');
});

it('filters performed procedures by the chosen years', function () {
    $from2024 = PerformedProcedure::factory()->performedOn('2024-06-01')->create();
    PerformedProcedure::factory()->performedOn('2025-06-01')->create();

    $response = $this->actingAsLoggedInUser()->getJson(route('api.performed-procedures.index', ['years' => [2024]]));

    expect($response->json('data.*.id'))->toBe([$from2024->id]);
});

it('filters performed and scheduled procedures', function (array $statuses, array $expected) {
    $procedures = [
        'performed' => PerformedProcedure::factory()->performedOn('2026-10-14')->create(),
        'scheduled' => PerformedProcedure::factory()->performedOn('2026-10-20')->create(),
    ];

    $response = $this->actingAsLoggedInUser()->getJson(route('api.performed-procedures.index', ['statuses' => $statuses]));

    expect($response->json('data.*.id'))->toEqualCanonicalizing(array_map(fn (string $name): string => $procedures[$name]->id, $expected));
})->with([
    'performed' => [['performed'], ['performed']],
    'scheduled' => [['scheduled'], ['scheduled']],
    'unknown statuses are ignored' => [['cancelled'], ['performed', 'scheduled']],
]);

it('lists the years that have performed procedures, newest first', function () {
    PerformedProcedure::factory()->performedOn('2024-06-01')->create();
    PerformedProcedure::factory()->performedOn('2026-02-01')->create();

    $response = $this->actingAsLoggedInUser()->getJson(route('api.performed-procedures.years'));

    expect($response->json())->toBe([2026, 2024]);
});

it('returns 401 to guests recording a procedure', function () {
    $response = $this->postJson(route('api.performed-procedures.store'), procedureEntryRequest());

    $response->assertUnauthorized();
    $this->assertDatabaseEmpty('Stay');
    $this->assertDatabaseEmpty('Undergoes');
});

it('admits the patient and records the first procedure of the stay together', function () {
    $request = procedureEntryRequest();

    $response = $this->actingAsLoggedInUser()->postJson(route('api.performed-procedures.store'), $request);

    $response->assertCreated()->assertJson(['procDate' => '2026-10-10', 'stay' => ['startDate' => '2026-10-10', 'endDate' => null]]);
    $stay = Stay::query()->sole();
    expect($stay->patient_id)->toBe($request['patient_id']);
    expect($stay->room_id)->toBe($request['room_id']);
    $this->assertDatabaseHas('Undergoes', [
        'stay_id' => $stay->id,
        'patient_id' => $request['patient_id'],
        'procedure_id' => $request['procedure_id'],
        'physician_id' => $request['physician_id'],
        'nurse_id' => $request['nurse_id'],
        'procDate' => '2026-10-10',
    ]);
});

it('schedules a future procedure with a planned stay', function () {
    $response = $this->actingAsLoggedInUser()->postJson(route('api.performed-procedures.store'), procedureEntryRequest([
        'procDate' => '2026-10-21',
        'startDate' => '2026-10-20',
        'endDate' => '2026-10-24',
    ]));

    $response->assertCreated();
    $this->assertDatabaseHas('Stay', ['startDate' => '2026-10-20', 'endDate' => '2026-10-24']);
    $this->assertDatabaseHas('Undergoes', ['procDate' => '2026-10-21']);
});

it('rejects a procedure dated outside its new stay', function (string $startDate, string $endDate, string $procDate, string $message) {
    $response = $this->actingAsLoggedInUser()->postJson(route('api.performed-procedures.store'), procedureEntryRequest([
        'startDate' => $startDate,
        'endDate' => $endDate,
        'procDate' => $procDate,
    ]));

    $response->assertUnprocessable()->assertJsonValidationErrors(['procDate' => $message]);
    $this->assertDatabaseEmpty('Stay');
})->with([
    'before admission' => ['2026-10-08', '', '2026-10-07', 'The procedure must be during the stay (2026-10-08 to now).'],
    'after discharge' => ['2026-10-08', '2026-10-12', '2026-10-13', 'The procedure must be during the stay (2026-10-08 to 2026-10-12).'],
]);

it('rejects an intern as the physician and admits nobody', function () {
    $intern = Physician::factory()->withPosition(PhysicianPosition::Intern)->create(['name' => 'Elliot Reid']);

    $response = $this->actingAsLoggedInUser()->postJson(route('api.performed-procedures.store'), procedureEntryRequest(['physician_id' => $intern->id]));

    $response->assertUnprocessable()->assertJsonValidationErrors([
        'physician_id' => 'Elliot Reid is an intern and cannot perform procedures.',
    ]);
    $this->assertDatabaseEmpty('Stay');
    $this->assertDatabaseEmpty('Undergoes');
});

it('returns 404 for anyone or anything that is no longer active', function (string $field) {
    $request = procedureEntryRequest();
    match ($field) {
        'patient_id' => Patient::query()->find($request['patient_id'])->delete(),
        'room_id' => Room::query()->find($request['room_id'])->delete(),
        'physician_id' => Physician::query()->find($request['physician_id'])->delete(),
        'nurse_id' => Nurse::query()->find($request['nurse_id'])->delete(),
        'procedure_id' => Procedure::query()->find($request['procedure_id'])->delete(),
    };

    $response = $this->actingAsLoggedInUser()->postJson(route('api.performed-procedures.store'), $request);

    $response->assertNotFound();
    $this->assertDatabaseEmpty('Stay');
})->with(['patient_id', 'room_id', 'physician_id', 'nurse_id', 'procedure_id']);

it('rejects a patient who already has a stay at that time', function () {
    $patient = registeredPatientForProcedure(['name' => 'Jordan Sullivan']);
    Stay::factory()->for($patient)->between('2026-10-05')->create();

    $response = $this->actingAsLoggedInUser()->postJson(route('api.performed-procedures.store'), procedureEntryRequest(['patient_id' => $patient->id]));

    $response->assertUnprocessable()->assertJsonValidationErrors([
        'patient_id' => 'Jordan Sullivan is already admitted since 2026-10-05. Add procedures from the Stays tab.',
    ]);
});

it('lets a patient be admitted again after the earlier stay ends', function () {
    $patient = registeredPatientForProcedure();
    Stay::factory()->for($patient)->between('2026-10-01', '2026-10-10')->create();

    $response = $this->actingAsLoggedInUser()->postJson(route('api.performed-procedures.store'), procedureEntryRequest(['patient_id' => $patient->id]));

    $response->assertCreated();
});

it('rejects an admission before the patient registered', function () {
    $this->travelTo('2026-03-01 10:00:00');
    $patient = Patient::factory()->create(['name' => 'Jordan Sullivan']);
    $this->travelTo('2026-10-15 12:00:00');

    $response = $this->actingAsLoggedInUser()->postJson(route('api.performed-procedures.store'), procedureEntryRequest([
        'patient_id' => $patient->id,
        'startDate' => '2026-02-28',
        'procDate' => '2026-02-28',
    ]));

    $response->assertUnprocessable()->assertJsonValidationErrors([
        'startDate' => 'Jordan Sullivan registered on 2026-03-01, so an admission cannot be dated earlier.',
    ]);
});

it('fills a single room with one patient and a double room with two', function (RoomType $roomType, int $patientsAlreadyThere) {
    $room = Room::factory()->withType($roomType)->create(['roomID' => 204]);
    Stay::factory()->count($patientsAlreadyThere)->for($room)->between('2026-10-05')->create();

    $response = $this->actingAsLoggedInUser()->postJson(route('api.performed-procedures.store'), procedureEntryRequest(['room_id' => $room->id]));

    $response->assertUnprocessable()->assertJsonValidationErrors([
        'room_id' => "Room 204 is full on 2026-10-10 ({$patientsAlreadyThere} of {$patientsAlreadyThere} beds taken).",
    ]);
})->with([
    'single room' => [RoomType::Single, 1],
    'double room' => [RoomType::Double, 2],
]);

it('admits a second patient to a double room', function () {
    $room = Room::factory()->withType(RoomType::Double)->create();
    Stay::factory()->for($room)->between('2026-10-05')->create();

    $response = $this->actingAsLoggedInUser()->postJson(route('api.performed-procedures.store'), procedureEntryRequest(['room_id' => $room->id]));

    $response->assertCreated();
});

it('frees a bed on the day its patient leaves', function () {
    $room = Room::factory()->withType(RoomType::Single)->create();
    Stay::factory()->for($room)->between('2026-10-01', '2026-10-10')->create();

    $response = $this->actingAsLoggedInUser()->postJson(route('api.performed-procedures.store'), procedureEntryRequest(['room_id' => $room->id]));

    $response->assertCreated();
});

it('lets a stay with an end date use a room that is booked later', function () {
    $room = Room::factory()->withType(RoomType::Single)->create();
    Stay::factory()->for($room)->between('2026-10-20', '2026-10-25')->create();

    $response = $this->actingAsLoggedInUser()->postJson(route('api.performed-procedures.store'), procedureEntryRequest([
        'room_id' => $room->id,
        'endDate' => '2026-10-20',
    ]));

    $response->assertCreated();
});

it('rejects a stay when the room fills up during it', function () {
    $room = Room::factory()->withType(RoomType::Single)->create(['roomID' => 204]);
    Stay::factory()->for($room)->between('2026-10-12', '2026-10-14')->create();

    $response = $this->actingAsLoggedInUser()->postJson(route('api.performed-procedures.store'), procedureEntryRequest([
        'room_id' => $room->id,
        'endDate' => '2026-10-13',
    ]));

    $response->assertUnprocessable()->assertJsonValidationErrors([
        'room_id' => 'Room 204 is full on 2026-10-12 (1 of 1 beds taken).',
    ]);
});

it('shows the MySQL error when the stay ends on or before its admission day', function () {
    config(['app.debug' => true]);

    $response = $this->actingAsLoggedInUser()->postJson(route('api.performed-procedures.store'), procedureEntryRequest(['endDate' => '2026-10-10']));

    $response->assertInternalServerError();
    expect($response->json('message'))->toContain('stay_dates_check');
    $this->assertDatabaseEmpty('Stay');
    $this->assertDatabaseEmpty('Undergoes');
});

it('has no way to change or remove a performed procedure', function (string $method) {
    $performedProcedure = PerformedProcedure::factory()->create();

    $response = $this->actingAsLoggedInUser()->json($method, "/api/performed-procedures/{$performedProcedure->id}", ['procDate' => '2026-01-01']);

    $response->assertNotFound();
    $this->assertNotSoftDeleted($performedProcedure);
})->with(['PUT', 'DELETE']);
