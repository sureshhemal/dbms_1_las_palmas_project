<?php

use App\Clinical\Entities\Models\Stay;
use App\Hospital\Entities\Models\Room;
use App\Hospital\Entities\RoomType;

beforeEach(function () {
    $this->travelTo('2026-10-15 12:00:00');
});

it('returns 401 to guests', function () {
    $response = $this->getJson(route('api.room-availability.index'));

    $response->assertUnauthorized();
});

it('lists each room with its beds taken and free today, by room id', function () {
    $single = Room::factory()->withType(RoomType::Single)->create(['roomID' => 102]);
    $double = Room::factory()->withType(RoomType::Double)->create(['roomID' => 101]);
    Stay::factory()->for($double)->between('2026-10-10')->create();
    Stay::factory()->for($single)->between('2026-10-01', '2026-10-05')->create();

    $response = $this->actingAsLoggedInUser()->getJson(route('api.room-availability.index'));

    $response->assertOk();
    expect($response->json())->toBe([
        ['id' => $double->id, 'roomID' => 101, 'roomType' => 'Double', 'beds' => 2, 'bedsTaken' => 1, 'bedsFree' => 1],
        ['id' => $single->id, 'roomID' => 102, 'roomType' => 'Single', 'beds' => 1, 'bedsTaken' => 0, 'bedsFree' => 1],
    ]);
});

it('leaves out full rooms', function () {
    $fullSingle = Room::factory()->withType(RoomType::Single)->create(['roomID' => 101]);
    $fullDouble = Room::factory()->withType(RoomType::Double)->create(['roomID' => 102]);
    $halfFullDouble = Room::factory()->withType(RoomType::Double)->create(['roomID' => 103]);
    Stay::factory()->for($fullSingle)->between('2026-10-12')->create();
    Stay::factory()->count(2)->for($fullDouble)->between('2026-10-12')->create();
    Stay::factory()->for($halfFullDouble)->between('2026-10-12')->create();

    $response = $this->actingAsLoggedInUser()->getJson(route('api.room-availability.index'));

    expect($response->json('*.roomID'))->toBe([103]);
});

it('lists the rooms with a free bed from the chosen date onwards', function (string $date, array $expectedRoomIds) {
    $singleTakenFromThe12th = Room::factory()->withType(RoomType::Single)->create(['roomID' => 101]);
    $singleTakenEarlyOctober = Room::factory()->withType(RoomType::Single)->create(['roomID' => 102]);
    Stay::factory()->for($singleTakenFromThe12th)->between('2026-10-12')->create();
    Stay::factory()->for($singleTakenEarlyOctober)->between('2026-10-01', '2026-10-05')->create();

    $response = $this->actingAsLoggedInUser()->getJson(route('api.room-availability.index', ['date' => $date]));

    expect($response->json('*.roomID'))->toBe($expectedRoomIds);
})->with([
    'during the early stay, before the later one' => ['2026-10-03', []],
    'on the discharge day of the early stay' => ['2026-10-05', [102]],
    'after the early stay, before the later one' => ['2026-10-06', [102]],
    'once the later stay has begun' => ['2026-10-13', [102]],
]);

it('counts the busiest day from the chosen date onwards', function () {
    $double = Room::factory()->withType(RoomType::Double)->create(['roomID' => 101]);
    Stay::factory()->for($double)->between('2026-10-01', '2026-10-14')->create();
    Stay::factory()->for($double)->between('2026-10-12')->create();

    $fullOnThe12th = $this->actingAsLoggedInUser()->getJson(route('api.room-availability.index', ['date' => '2026-10-03']));
    $freeAgainFromThe14th = $this->actingAsLoggedInUser()->getJson(route('api.room-availability.index', ['date' => '2026-10-14']));

    expect($fullOnThe12th->json())->toBe([]);
    expect($freeAgainFromThe14th->json('0'))->toMatchArray(['roomID' => 101, 'beds' => 2, 'bedsTaken' => 1, 'bedsFree' => 1]);
});

it('lists the rooms with a free bed between the chosen dates', function (string $until, array $expectedRoomIds) {
    $single = Room::factory()->withType(RoomType::Single)->create(['roomID' => 106]);
    Stay::factory()->for($single)->between('2026-10-09', '2026-10-10')->create();
    Stay::factory()->for($single)->between('2026-10-13')->create();

    $response = $this->actingAsLoggedInUser()->getJson(route('api.room-availability.index', ['date' => '2026-10-05', 'until' => $until]));

    expect($response->json('*.roomID'))->toBe($expectedRoomIds);
})->with([
    'leaving before the next patient arrives' => ['2026-10-08', [106]],
    'leaving on the day the next patient arrives' => ['2026-10-09', [106]],
    'still there when the next patient arrives' => ['2026-10-10', []],
]);

it('uses the hospital today when no date is chosen', function () {
    $single = Room::factory()->withType(RoomType::Single)->create(['roomID' => 101]);
    Stay::factory()->for($single)->between('2026-10-01', '2026-10-10')->create();

    $response = $this->actingAsLoggedInUser()->getJson(route('api.room-availability.index'));

    expect($response->json('*.roomID'))->toBe([101]);
});

it('leaves out decommissioned rooms', function () {
    Room::factory()->create()->delete();

    $response = $this->actingAsLoggedInUser()->getJson(route('api.room-availability.index'));

    $response->assertOk()->assertJsonCount(0);
});
