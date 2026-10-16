<?php

use App\Clinical\Entities\Models\Stay;
use App\Hospital\Entities\Models\Room;
use App\Hospital\Entities\RoomType;
use App\Patient\Entities\Models\Patient;

beforeEach(function () {
    $this->travelTo('2026-10-15 12:00:00');
});

function registeredPatient(array $attributes = []): Patient
{
    return Patient::factory()->create(['created_at' => '2020-01-01 09:00:00', ...$attributes]);
}

function admissionRequest(array $overrides = []): array
{
    return [
        'patient_id' => registeredPatient()->id,
        'room_id' => Room::factory()->withType(RoomType::Double)->create()->id,
        'startDate' => '2026-10-10',
        ...$overrides,
    ];
}

it('returns 401 to guests', function () {
    $response = $this->getJson(route('api.stays.index'));

    $response->assertUnauthorized();
});

it('lists the stays on the first page, latest admission first', function () {
    $older = Stay::factory()->between('2026-01-01', '2026-01-05')->create();
    $current = Stay::factory()->between('2026-10-12')->create();
    $middle = Stay::factory()->between('2026-05-01', '2026-05-09')->create();

    $response = $this->actingAsLoggedInUser()->getJson(route('api.stays.index'));

    $response->assertOk()->assertJson(['current_page' => 1, 'per_page' => 20, 'total' => 3]);
    expect($response->json('data.*.id'))->toBe([$current->id, $middle->id, $older->id]);
});

it('includes the patient and room of each stay', function () {
    $patient = registeredPatient(['name' => 'Jordan Sullivan', 'patientID' => 321]);
    $room = Room::factory()->withType(RoomType::Single)->create(['roomID' => 204]);
    Stay::factory()->for($patient)->for($room)->between('2026-10-01', '2026-10-04')->create();

    $response = $this->actingAsLoggedInUser()->getJson(route('api.stays.index'));

    expect($response->json('data.0'))->toMatchArray(['startDate' => '2026-10-01', 'endDate' => '2026-10-04']);
    expect($response->json('data.0.patient'))->toMatchArray(['id' => $patient->id, 'patientID' => 321, 'name' => 'Jordan Sullivan', 'deleted_at' => null]);
    expect($response->json('data.0.room'))->toMatchArray(['id' => $room->id, 'roomID' => 204, 'roomType' => 'Single', 'deleted_at' => null]);
});

it('filters current and discharged stays', function (string $status, string $expectedStay) {
    $stays = [
        'current' => Stay::factory()->between('2026-10-12')->create(),
        'discharged' => Stay::factory()->between('2026-10-01', '2026-10-04')->create(),
    ];

    $response = $this->actingAsLoggedInUser()->getJson(route('api.stays.index', ['status' => $status]));

    expect($response->json('data.*.id'))->toBe([$stays[$expectedStay]->id]);
})->with([
    'current' => ['current', 'current'],
    'discharged' => ['discharged', 'discharged'],
]);

it('finds stays by patient name, patient id or room id', function (string $search) {
    $stay = Stay::factory()
        ->for(registeredPatient(['name' => 'Jordan Sullivan', 'patientID' => 321]))
        ->for(Room::factory()->state(['roomID' => 204]))
        ->create();
    Stay::factory()
        ->for(registeredPatient(['name' => 'Carla Espinosa', 'patientID' => 654]))
        ->for(Room::factory()->state(['roomID' => 515]))
        ->create();

    $response = $this->actingAsLoggedInUser()->getJson(route('api.stays.index', ['search' => $search]));

    expect($response->json('data.*.id'))->toBe([$stay->id]);
})->with([
    'patient name' => ['jordan'],
    'patient id' => ['321'],
    'room id' => ['204'],
]);

it('returns 401 to guests admitting a patient', function () {
    $response = $this->postJson(route('api.stays.store'), admissionRequest());

    $response->assertUnauthorized();
    $this->assertDatabaseEmpty('Stay');
});

it('admits a patient to a room', function () {
    $request = admissionRequest();

    $response = $this->actingAsLoggedInUser()->postJson(route('api.stays.store'), $request);

    $response->assertCreated()->assertJson(['startDate' => '2026-10-10', 'endDate' => null]);
    expect($response->json('stayID'))->toBeInt();
    expect($response->json('patient.id'))->toBe($request['patient_id']);
    $this->assertDatabaseHas('Stay', [...$request, 'endDate' => null]);
});

it('rejects an admission dated in the future', function () {
    $response = $this->actingAsLoggedInUser()->postJson(route('api.stays.store'), admissionRequest(['startDate' => '2026-10-16']));

    $response->assertUnprocessable()->assertJsonValidationErrors(['startDate' => 'An admission cannot be dated in the future.']);
    $this->assertDatabaseEmpty('Stay');
});

it('rejects an admission before the patient registered', function () {
    $this->travelTo('2026-03-01 10:00:00');
    $patient = Patient::factory()->create(['name' => 'Jordan Sullivan']);
    $this->travelTo('2026-10-15 12:00:00');

    $response = $this->actingAsLoggedInUser()->postJson(route('api.stays.store'), admissionRequest([
        'patient_id' => $patient->id,
        'startDate' => '2026-02-28',
    ]));

    $response->assertUnprocessable()->assertJsonValidationErrors([
        'startDate' => 'Jordan Sullivan registered on 2026-03-01, so an admission cannot be dated earlier.',
    ]);
});

it('returns 404 for an inactive patient or a decommissioned room', function (string $field) {
    $patient = registeredPatient();
    $room = Room::factory()->create();
    $field === 'patient_id' ? $patient->delete() : $room->delete();

    $response = $this->actingAsLoggedInUser()->postJson(route('api.stays.store'), admissionRequest([
        'patient_id' => $patient->id,
        'room_id' => $room->id,
    ]));

    $response->assertNotFound();
    $this->assertDatabaseEmpty('Stay');
})->with(['patient_id', 'room_id']);

it('rejects admitting a patient who is already admitted', function () {
    $patient = registeredPatient(['name' => 'Jordan Sullivan']);
    Stay::factory()->for($patient)->between('2026-10-05')->create();

    $response = $this->actingAsLoggedInUser()->postJson(route('api.stays.store'), admissionRequest(['patient_id' => $patient->id]));

    $response->assertUnprocessable()->assertJsonValidationErrors([
        'patient_id' => 'Jordan Sullivan is already admitted since 2026-10-05.',
    ]);
});

it('lets a patient be admitted again after being discharged', function () {
    $patient = registeredPatient();
    Stay::factory()->for($patient)->between('2026-10-01', '2026-10-10')->create();

    $response = $this->actingAsLoggedInUser()->postJson(route('api.stays.store'), admissionRequest([
        'patient_id' => $patient->id,
        'startDate' => '2026-10-10',
    ]));

    $response->assertCreated();
});

it('fills a single room with one patient and a double room with two', function (RoomType $roomType, int $patientsAlreadyThere) {
    $room = Room::factory()->withType($roomType)->create(['roomID' => 204]);
    Stay::factory()->count($patientsAlreadyThere)->for($room)->between('2026-10-05')->create();

    $response = $this->actingAsLoggedInUser()->postJson(route('api.stays.store'), admissionRequest(['room_id' => $room->id]));

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

    $response = $this->actingAsLoggedInUser()->postJson(route('api.stays.store'), admissionRequest(['room_id' => $room->id]));

    $response->assertCreated();
});

it('frees a bed on the day its patient is discharged', function () {
    $room = Room::factory()->withType(RoomType::Single)->create();
    Stay::factory()->for($room)->between('2026-10-01', '2026-10-10')->create();

    $response = $this->actingAsLoggedInUser()->postJson(route('api.stays.store'), admissionRequest([
        'room_id' => $room->id,
        'startDate' => '2026-10-10',
    ]));

    $response->assertCreated();
});

it('counts only the patients in the room at the same time', function () {
    $room = Room::factory()->withType(RoomType::Double)->create();
    Stay::factory()->for($room)->between('2026-10-01', '2026-10-06')->create();
    Stay::factory()->for($room)->between('2026-10-12')->create();

    $response = $this->actingAsLoggedInUser()->postJson(route('api.stays.store'), admissionRequest([
        'room_id' => $room->id,
        'startDate' => '2026-10-03',
    ]));

    $response->assertCreated();
});

it('records a past stay together with its discharge date', function () {
    $request = admissionRequest(['startDate' => '2026-10-05', 'endDate' => '2026-10-08']);

    $response = $this->actingAsLoggedInUser()->postJson(route('api.stays.store'), $request);

    $response->assertCreated()->assertJson(['startDate' => '2026-10-05', 'endDate' => '2026-10-08']);
    $this->assertDatabaseHas('Stay', ['patient_id' => $request['patient_id'], 'startDate' => '2026-10-05', 'endDate' => '2026-10-08']);
});

it('lets a past stay use a room that someone else takes later', function () {
    $room = Room::factory()->withType(RoomType::Single)->create();
    Stay::factory()->for($room)->between('2026-10-09')->create();

    $response = $this->actingAsLoggedInUser()->postJson(route('api.stays.store'), admissionRequest([
        'room_id' => $room->id,
        'startDate' => '2026-10-05',
        'endDate' => '2026-10-09',
    ]));

    $response->assertCreated();
});

it('rejects a past stay when the room fills up during it', function () {
    $room = Room::factory()->withType(RoomType::Single)->create(['roomID' => 204]);
    Stay::factory()->for($room)->between('2026-10-07', '2026-10-10')->create();

    $response = $this->actingAsLoggedInUser()->postJson(route('api.stays.store'), admissionRequest([
        'room_id' => $room->id,
        'startDate' => '2026-10-05',
        'endDate' => '2026-10-08',
    ]));

    $response->assertUnprocessable()->assertJsonValidationErrors([
        'room_id' => 'Room 204 is full on 2026-10-07 (1 of 1 beds taken).',
    ]);
});

it('lets a patient have a past stay before a later one', function () {
    $patient = registeredPatient();
    Stay::factory()->for($patient)->between('2026-10-10', '2026-10-12')->create();

    $response = $this->actingAsLoggedInUser()->postJson(route('api.stays.store'), admissionRequest([
        'patient_id' => $patient->id,
        'startDate' => '2026-10-05',
        'endDate' => '2026-10-08',
    ]));

    $response->assertCreated();
});

it('rejects a past stay that overlaps another stay of the same patient', function () {
    $patient = registeredPatient(['name' => 'Jordan Sullivan']);
    Stay::factory()->for($patient)->between('2026-10-07', '2026-10-10')->create();

    $response = $this->actingAsLoggedInUser()->postJson(route('api.stays.store'), admissionRequest([
        'patient_id' => $patient->id,
        'startDate' => '2026-10-05',
        'endDate' => '2026-10-08',
    ]));

    $response->assertUnprocessable()->assertJsonValidationErrors([
        'patient_id' => 'Jordan Sullivan is already admitted since 2026-10-07.',
    ]);
});

it('rejects recording a stay that ends in the future', function () {
    $response = $this->actingAsLoggedInUser()->postJson(route('api.stays.store'), admissionRequest([
        'startDate' => '2026-10-05',
        'endDate' => '2026-10-16',
    ]));

    $response->assertUnprocessable()->assertJsonValidationErrors(['endDate' => 'A discharge cannot be dated in the future.']);
    $this->assertDatabaseEmpty('Stay');
});

it('shows the MySQL error when a recorded stay ends on or before its admission day', function () {
    config(['app.debug' => true]);

    $response = $this->actingAsLoggedInUser()->postJson(route('api.stays.store'), admissionRequest([
        'startDate' => '2026-10-05',
        'endDate' => '2026-10-05',
    ]));

    $response->assertInternalServerError();
    expect($response->json('message'))->toContain('stay_dates_check');
});

it('returns 401 to guests discharging a patient', function () {
    $stay = Stay::factory()->between('2026-10-05')->create();

    $response = $this->patchJson(route('api.stays.discharge', $stay), ['endDate' => '2026-10-12']);

    $response->assertUnauthorized();
    expect($stay->fresh()->endDate)->toBeNull();
});

it('discharges a patient', function () {
    $stay = Stay::factory()->between('2026-10-05')->create();

    $response = $this->actingAsLoggedInUser()->patchJson(route('api.stays.discharge', $stay), ['endDate' => '2026-10-12']);

    $response->assertOk()->assertJson(['id' => $stay->id, 'startDate' => '2026-10-05', 'endDate' => '2026-10-12']);
    expect($stay->fresh()->endDate->toDateString())->toBe('2026-10-12');
});

it('returns 404 when discharging a stay that does not exist', function () {
    $response = $this->actingAsLoggedInUser()->patchJson(route('api.stays.discharge', (string) str()->ulid()), ['endDate' => '2026-10-12']);

    $response->assertNotFound();
});

it('rejects a discharge dated in the future', function () {
    $stay = Stay::factory()->between('2026-10-05')->create();

    $response = $this->actingAsLoggedInUser()->patchJson(route('api.stays.discharge', $stay), ['endDate' => '2026-10-16']);

    $response->assertUnprocessable()->assertJsonValidationErrors(['endDate' => 'A discharge cannot be dated in the future.']);
});

it('rejects discharging a patient twice', function () {
    $stay = Stay::factory()->between('2026-10-01', '2026-10-04')->create();

    $response = $this->actingAsLoggedInUser()->patchJson(route('api.stays.discharge', $stay), ['endDate' => '2026-10-12']);

    $response->assertUnprocessable()->assertJsonValidationErrors(['endDate' => 'This stay was already discharged on 2026-10-04.']);
    expect($stay->fresh()->endDate->toDateString())->toBe('2026-10-04');
});

it('shows the MySQL error when discharging on or before the admission day', function () {
    config(['app.debug' => true]);
    $stay = Stay::factory()->between('2026-10-05')->create();

    $response = $this->actingAsLoggedInUser()->patchJson(route('api.stays.discharge', $stay), ['endDate' => '2026-10-05']);

    $response->assertInternalServerError();
    expect($response->json('message'))->toContain('stay_dates_check');
    expect($stay->fresh()->endDate)->toBeNull();
});

it('has no way to change or remove a stay', function (string $method) {
    $stay = Stay::factory()->between('2026-10-05')->create();

    $response = $this->actingAsLoggedInUser()->json($method, "/api/stays/{$stay->id}", ['startDate' => '2026-01-01']);

    $response->assertNotFound();
    $this->assertNotSoftDeleted($stay);
    expect($stay->fresh()->startDate->toDateString())->toBe('2026-10-05');
})->with(['PUT', 'DELETE']);
