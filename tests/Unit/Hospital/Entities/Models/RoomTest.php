<?php

use App\Hospital\Entities\Models\Room;
use App\Hospital\Entities\RoomType;
use Illuminate\Database\QueryException;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Support\Facades\DB;

it('stores the room type as the text allowed by the schema', function () {
    $room = Room::create(['roomID' => 1, 'roomType' => RoomType::Double]);

    $this->assertDatabaseHas('Room', ['id' => $room->id, 'roomType' => 'Double']);
    expect(Room::find($room->id)->roomType)->toBe(RoomType::Double);
});

it('generates a ulid primary key', function () {
    $room = Room::create(['roomID' => 2, 'roomType' => RoomType::Single]);

    expect($room->getKey())->toBeString()->toHaveLength(26);
});

it('rejects a room type outside the schema list', function () {
    DB::table('Room')->insert(['roomID' => 3, 'id' => (string) str()->ulid(), 'roomType' => 'Suite']);
})->throws(QueryException::class);

it('keeps the row when a room is deleted', function () {
    $room = Room::create(['roomID' => 4, 'roomType' => RoomType::Single]);

    $room->delete();

    $this->assertSoftDeleted($room);
});

it('rejects a duplicate roomID', function () {
    Room::create(['roomID' => 1, 'roomType' => RoomType::Single]);

    Room::create(['roomID' => 1, 'roomType' => RoomType::Single]);
})->throws(UniqueConstraintViolationException::class);

it('assigns the next roomID when none is given', function () {
    Room::create(['roomID' => 900000, 'roomType' => RoomType::Single]);

    $room = Room::create(['roomType' => RoomType::Single]);

    expect($room->roomID)->toBe(900001);
    expect(Room::find($room->id)->roomID)->toBe(900001);
});
