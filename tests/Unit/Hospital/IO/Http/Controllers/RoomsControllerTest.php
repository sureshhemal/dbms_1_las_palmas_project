<?php

use App\Hospital\Entities\Models\Room;
use App\Hospital\Entities\RoomType;

it('returns 401 to guests', function () {
    $response = $this->getJson(route('api.rooms.index'));

    $response->assertUnauthorized();
});

it('lists the rooms on the first page in room id order', function () {
    Room::factory()->create(['roomID' => 203]);
    Room::factory()->create(['roomID' => 101]);
    Room::factory()->create(['roomID' => 102]);

    $response = $this->actingAsLoggedInUser()->getJson(route('api.rooms.index'));

    $response->assertOk()->assertJsonCount(3, 'data')->assertJson(['current_page' => 1, 'per_page' => 20, 'total' => 3]);
    expect($response->json('data.*.roomID'))->toBe([101, 102, 203]);
});

it('returns the requested page of rooms', function () {
    foreach (range(101, 125) as $roomID) {
        Room::factory()->create(['roomID' => $roomID]);
    }

    $response = $this->actingAsLoggedInUser()->getJson(route('api.rooms.index', ['page' => 2, 'per_page' => 10]));

    $response->assertOk()->assertJson([
        'current_page' => 2,
        'per_page' => 10,
        'from' => 11,
        'to' => 20,
        'total' => 25,
        'last_page' => 3,
    ]);
    expect($response->json('data.*.roomID'))->toBe(range(111, 120));
});

it('counts only the matching rooms in the page details', function () {
    Room::factory()->withType(RoomType::Single)->count(3)->create();
    Room::factory()->withType(RoomType::Double)->count(2)->create();

    $response = $this->actingAsLoggedInUser()->getJson(route('api.rooms.index', ['room_types' => ['Double']]));

    expect($response->json('total'))->toBe(2);
});

it('leaves out removed rooms', function () {
    $keptRoom = Room::factory()->create();
    Room::factory()->create()->delete();

    $response = $this->actingAsLoggedInUser()->getJson(route('api.rooms.index'));

    expect($response->json('data.*.id'))->toBe([$keptRoom->id]);
});

it('finds rooms by room id', function () {
    $room = Room::factory()->create(['roomID' => 1201]);
    Room::factory()->create(['roomID' => 305]);

    $response = $this->actingAsLoggedInUser()->getJson(route('api.rooms.index', ['search' => '120']));

    expect($response->json('data.*.id'))->toBe([$room->id]);
});

it('filters rooms by the chosen room types', function () {
    $singleRoom = Room::factory()->withType(RoomType::Single)->create();
    Room::factory()->withType(RoomType::Double)->create();

    $response = $this->actingAsLoggedInUser()->getJson(route('api.rooms.index', ['room_types' => ['Single']]));

    expect($response->json('data.*.id'))->toBe([$singleRoom->id]);
});

it('ignores room types that do not exist', function () {
    Room::factory()->count(2)->create();

    $response = $this->actingAsLoggedInUser()->getJson(route('api.rooms.index', ['room_types' => ['Suite']]));

    $response->assertJsonCount(2, 'data');
});

it('combines the search and the room type filter', function () {
    $doubleRoom = Room::factory()->withType(RoomType::Double)->create(['roomID' => 1201]);
    Room::factory()->withType(RoomType::Single)->create(['roomID' => 1202]);
    Room::factory()->withType(RoomType::Double)->create(['roomID' => 305]);

    $response = $this->actingAsLoggedInUser()->getJson(route('api.rooms.index', [
        'search' => '120',
        'room_types' => ['Double'],
    ]));

    expect($response->json('data.*.id'))->toBe([$doubleRoom->id]);
});

it('returns 401 to guests adding a room', function () {
    $response = $this->postJson(route('api.rooms.store'), [
        'roomID' => 101,
        'roomType' => 'Single',
    ]);

    $response->assertUnauthorized();
    $this->assertDatabaseEmpty('Room');
});

it('adds a room', function () {
    $response = $this->actingAsLoggedInUser()->postJson(route('api.rooms.store'), [
        'roomID' => 101,
        'roomType' => 'Double',
    ]);

    $response->assertCreated()->assertJson(['roomID' => 101, 'roomType' => 'Double']);
    $this->assertDatabaseHas('Room', ['roomID' => 101, 'roomType' => RoomType::Double->value]);
});

it('assigns the room id when it is left blank', function () {
    Room::factory()->create(['roomID' => 120]);

    $response = $this->actingAsLoggedInUser()->postJson(route('api.rooms.store'), [
        'roomID' => '',
        'roomType' => 'Single',
    ]);

    $response->assertCreated();
    expect($response->json('roomID'))->toBeInt()->toBeGreaterThan(120);
});

it('shows the MySQL error when the room id is already taken', function () {
    config(['app.debug' => true]);
    Room::factory()->create(['roomID' => 101]);

    $response = $this->actingAsLoggedInUser()->postJson(route('api.rooms.store'), [
        'roomID' => 101,
        'roomType' => 'Single',
    ]);

    $response->assertInternalServerError();
    expect($response->json('message'))->toContain("Duplicate entry '101'");
    $this->assertDatabaseCount('Room', 1);
});

it('shows the MySQL error when the room id belongs to a removed room', function () {
    config(['app.debug' => true]);
    Room::factory()->create(['roomID' => 101])->delete();

    $response = $this->actingAsLoggedInUser()->postJson(route('api.rooms.store'), [
        'roomID' => 101,
        'roomType' => 'Single',
    ]);

    $response->assertInternalServerError();
    expect($response->json('message'))->toContain("Duplicate entry '101'");
});

it('shows the MySQL error when the room type is left blank', function () {
    config(['app.debug' => true]);

    $response = $this->actingAsLoggedInUser()->postJson(route('api.rooms.store'), [
        'roomID' => 101,
        'roomType' => '',
    ]);

    $response->assertInternalServerError();
    expect($response->json('message'))->toContain("Column 'roomType' cannot be null");
    $this->assertDatabaseEmpty('Room');
});

it('returns 401 to guests updating a room', function () {
    $room = Room::factory()->withType(RoomType::Single)->create(['roomID' => 101]);

    $response = $this->putJson(route('api.rooms.update', $room), [
        'roomID' => 102,
        'roomType' => 'Double',
    ]);

    $response->assertUnauthorized();
    expect($room->fresh()->roomID)->toBe(101);
});

it('updates a room', function () {
    $room = Room::factory()->withType(RoomType::Single)->create(['roomID' => 101]);

    $response = $this->actingAsLoggedInUser()->putJson(route('api.rooms.update', $room), [
        'roomID' => 202,
        'roomType' => 'Double',
    ]);

    $response->assertOk()->assertJson(['id' => $room->id, 'roomID' => 202, 'roomType' => 'Double']);
    $this->assertDatabaseHas('Room', ['id' => $room->id, 'roomID' => 202, 'roomType' => RoomType::Double->value]);
});

it('keeps the room id when it is left blank', function () {
    $room = Room::factory()->withType(RoomType::Single)->create(['roomID' => 101]);

    $response = $this->actingAsLoggedInUser()->putJson(route('api.rooms.update', $room), [
        'roomID' => '',
        'roomType' => 'Double',
    ]);

    $response->assertOk();
    $this->assertDatabaseHas('Room', ['id' => $room->id, 'roomID' => 101, 'roomType' => RoomType::Double->value]);
});

it('shows the MySQL error when updating to a room id that is already taken', function () {
    config(['app.debug' => true]);
    Room::factory()->create(['roomID' => 101]);
    $room = Room::factory()->create(['roomID' => 202]);

    $response = $this->actingAsLoggedInUser()->putJson(route('api.rooms.update', $room), [
        'roomID' => 101,
        'roomType' => 'Single',
    ]);

    $response->assertInternalServerError();
    expect($response->json('message'))->toContain("Duplicate entry '101'");
    expect($room->fresh()->roomID)->toBe(202);
});

it('returns 401 to guests removing a room', function () {
    $room = Room::factory()->create();

    $response = $this->deleteJson(route('api.rooms.destroy', $room));

    $response->assertUnauthorized();
    $this->assertNotSoftDeleted($room);
});

it('removes a room by soft deleting it', function () {
    $room = Room::factory()->create();

    $response = $this->actingAsLoggedInUser()->deleteJson(route('api.rooms.destroy', $room));

    $response->assertNoContent();
    $this->assertSoftDeleted($room);
});

it('returns 404 when removing a room that is already removed', function () {
    $room = Room::factory()->create();
    $room->delete();

    $response = $this->actingAsLoggedInUser()->deleteJson(route('api.rooms.destroy', $room));

    $response->assertNotFound();
});
