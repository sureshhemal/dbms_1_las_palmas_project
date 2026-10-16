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

function registeredPatient(array $attributes = []): Patient
{
    return Patient::factory()->create(['created_at' => '2020-01-01 09:00:00', ...$attributes]);
}

function stayProcedureRequest(array $overrides = []): array
{
    return [
        'procedure_id' => Procedure::factory()->create()->id,
        'physician_id' => Physician::factory()->withPosition(PhysicianPosition::Surgeon)->create()->id,
        'nurse_id' => Nurse::factory()->create()->id,
        'procDate' => '2026-10-07',
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

it('filters stays by admitted, scheduled and discharged', function (array $statuses, array $expectedStays) {
    $stays = [
        'open' => Stay::factory()->between('2026-10-12')->create(),
        'planned to end later' => Stay::factory()->between('2026-10-12', '2026-10-20')->create(),
        'ending today' => Stay::factory()->between('2026-10-10', '2026-10-15')->create(),
        'starting tomorrow' => Stay::factory()->between('2026-10-16', '2026-10-19')->create(),
        'over' => Stay::factory()->between('2026-10-01', '2026-10-04')->create(),
    ];

    $response = $this->actingAsLoggedInUser()->getJson(route('api.stays.index', ['statuses' => $statuses]));

    expect($response->json('data.*.id'))->toEqualCanonicalizing(array_map(fn (string $name): string => $stays[$name]->id, $expectedStays));
})->with([
    'admitted' => [['admitted'], ['open', 'planned to end later', 'ending today']],
    'scheduled' => [['scheduled'], ['starting tomorrow']],
    'discharged' => [['discharged'], ['over']],
    'admitted and scheduled' => [['admitted', 'scheduled'], ['open', 'planned to end later', 'ending today', 'starting tomorrow']],
]);

it('ignores statuses that do not exist', function () {
    Stay::factory()->count(2)->create();

    $response = $this->actingAsLoggedInUser()->getJson(route('api.stays.index', ['statuses' => ['cancelled']]));

    $response->assertJsonCount(2, 'data');
});

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

it('finds a stay by its stay id', function () {
    $stay = Stay::factory()
        ->for(registeredPatient(['patientID' => 11]))
        ->for(Room::factory()->state(['roomID' => 22]))
        ->create(['stayID' => 777001]);
    Stay::factory()
        ->for(registeredPatient(['patientID' => 33]))
        ->for(Room::factory()->state(['roomID' => 44]))
        ->create(['stayID' => 555002]);

    $response = $this->actingAsLoggedInUser()->getJson(route('api.stays.index', ['search' => '777001']));

    expect($response->json('data.*.id'))->toBe([$stay->id]);
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

it('plans a discharge in the future', function () {
    $stay = Stay::factory()->between('2026-10-05')->create();

    $response = $this->actingAsLoggedInUser()->patchJson(route('api.stays.discharge', $stay), ['endDate' => '2026-10-20']);

    $response->assertOk();
    expect($stay->fresh()->endDate->toDateString())->toBe('2026-10-20');
});

it('brings a planned discharge forward', function () {
    $stay = Stay::factory()->between('2026-10-05', '2026-10-20')->create();

    $response = $this->actingAsLoggedInUser()->patchJson(route('api.stays.discharge', $stay), ['endDate' => '2026-10-15']);

    $response->assertOk();
    expect($stay->fresh()->endDate->toDateString())->toBe('2026-10-15');
});

it('rejects a discharge before the last procedure of the stay', function () {
    $stay = Stay::factory()->between('2026-10-05')->create();
    PerformedProcedure::factory()->for($stay)->state(['patient_id' => $stay->patient_id, 'procDate' => '2026-10-12'])->create();

    $response = $this->actingAsLoggedInUser()->patchJson(route('api.stays.discharge', $stay), ['endDate' => '2026-10-11']);

    $response->assertUnprocessable()->assertJsonValidationErrors(['endDate' => 'The stay has a procedure on 2026-10-12, so it cannot end earlier.']);
    expect($stay->fresh()->endDate)->toBeNull();
});

it('rejects discharging a stay that is already over', function () {
    $stay = Stay::factory()->between('2026-10-01', '2026-10-04')->create();

    $response = $this->actingAsLoggedInUser()->patchJson(route('api.stays.discharge', $stay), ['endDate' => '2026-10-12']);

    $response->assertUnprocessable()->assertJsonValidationErrors(['endDate' => 'This stay already ended on 2026-10-04.']);
    expect($stay->fresh()->endDate->toDateString())->toBe('2026-10-04');
});

it('returns 401 to guests extending a stay', function () {
    $stay = Stay::factory()->between('2026-10-05', '2026-10-18')->create();

    $response = $this->patchJson(route('api.stays.extend', $stay), ['endDate' => '2026-10-20']);

    $response->assertUnauthorized();
});

it('extends a stay to a later end date', function () {
    $stay = Stay::factory()->between('2026-10-05', '2026-10-18')->create();

    $response = $this->actingAsLoggedInUser()->patchJson(route('api.stays.extend', $stay), ['endDate' => '2026-10-22']);

    $response->assertOk()->assertJson(['id' => $stay->id, 'endDate' => '2026-10-22']);
    expect($stay->fresh()->endDate->toDateString())->toBe('2026-10-22');
});

it('rejects extending to a date that is not later', function () {
    $stay = Stay::factory()->between('2026-10-05', '2026-10-18')->create();

    $response = $this->actingAsLoggedInUser()->patchJson(route('api.stays.extend', $stay), ['endDate' => '2026-10-18']);

    $response->assertUnprocessable()->assertJsonValidationErrors(['endDate' => 'The new end date must be after 2026-10-18.']);
});

it('rejects extending a stay that has no end date yet', function () {
    $stay = Stay::factory()->between('2026-10-05')->create();

    $response = $this->actingAsLoggedInUser()->patchJson(route('api.stays.extend', $stay), ['endDate' => '2026-10-22']);

    $response->assertUnprocessable()->assertJsonValidationErrors(['endDate' => 'This stay has no end date yet, so there is nothing to extend.']);
});

it('rejects extending a stay that is already over', function () {
    $stay = Stay::factory()->between('2026-10-01', '2026-10-04')->create();

    $response = $this->actingAsLoggedInUser()->patchJson(route('api.stays.extend', $stay), ['endDate' => '2026-10-22']);

    $response->assertUnprocessable()->assertJsonValidationErrors(['endDate' => 'This stay already ended on 2026-10-04.']);
});

it('rejects extending into days when the room is full', function () {
    $room = Room::factory()->withType(RoomType::Single)->create(['roomID' => 204]);
    $stay = Stay::factory()->for($room)->between('2026-10-05', '2026-10-18')->create();
    Stay::factory()->for($room)->between('2026-10-20', '2026-10-25')->create();

    $response = $this->actingAsLoggedInUser()->patchJson(route('api.stays.extend', $stay), ['endDate' => '2026-10-22']);

    $response->assertUnprocessable()->assertJsonValidationErrors(['endDate' => 'Room 204 is full on 2026-10-20 (1 of 1 beds taken).']);
    expect($stay->fresh()->endDate->toDateString())->toBe('2026-10-18');
});

it('rejects extending into another stay of the same patient', function () {
    $patient = registeredPatient(['name' => 'Jordan Sullivan']);
    $stay = Stay::factory()->for($patient)->between('2026-10-05', '2026-10-18')->create();
    Stay::factory()->for($patient)->between('2026-10-20', '2026-10-25')->create();

    $response = $this->actingAsLoggedInUser()->patchJson(route('api.stays.extend', $stay), ['endDate' => '2026-10-22']);

    $response->assertUnprocessable()->assertJsonValidationErrors(['endDate' => 'Jordan Sullivan has another stay from 2026-10-20.']);
});

it('returns 401 to guests adding a procedure to a stay', function () {
    $stay = Stay::factory()->between('2026-10-05')->create();

    $response = $this->postJson(route('api.stays.procedures.store', $stay), stayProcedureRequest());

    $response->assertUnauthorized();
});

it('adds a procedure to a stay for the patient of the stay', function () {
    $stay = Stay::factory()->between('2026-10-05')->create();
    $request = stayProcedureRequest(['procDate' => '2026-10-17']);

    $response = $this->actingAsLoggedInUser()->postJson(route('api.stays.procedures.store', $stay), $request);

    $response->assertCreated()->assertJson(['procDate' => '2026-10-17']);
    $this->assertDatabaseHas('Undergoes', [...$request, 'stay_id' => $stay->id, 'patient_id' => $stay->patient_id]);
});

it('rejects adding a procedure outside the stay', function () {
    $stay = Stay::factory()->between('2026-10-05', '2026-10-10')->create();

    $response = $this->actingAsLoggedInUser()->postJson(route('api.stays.procedures.store', $stay), stayProcedureRequest(['procDate' => '2026-10-11']));

    $response->assertUnprocessable()->assertJsonValidationErrors(['procDate' => 'The procedure must be during the stay (2026-10-05 to 2026-10-10).']);
});

it('shows the MySQL error when the same procedure is added twice on the same day of a stay', function () {
    config(['app.debug' => true]);
    $stay = Stay::factory()->between('2026-10-05')->create();
    $request = stayProcedureRequest();
    $this->actingAsLoggedInUser()->postJson(route('api.stays.procedures.store', $stay), $request)->assertCreated();

    $response = $this->actingAsLoggedInUser()->postJson(route('api.stays.procedures.store', $stay), $request);

    $response->assertInternalServerError();
    expect($response->json('message'))->toContain('Duplicate entry');
});

it('no longer admits patients without a procedure', function () {
    $response = $this->actingAsLoggedInUser()->postJson('/api/stays', [
        'patient_id' => registeredPatient()->id,
        'room_id' => Room::factory()->create()->id,
        'startDate' => '2026-10-10',
    ]);

    $response->assertMethodNotAllowed();
    $this->assertDatabaseEmpty('Stay');
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
