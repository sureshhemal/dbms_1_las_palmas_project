<?php

use App\Clinical\Entities\Models\Appointment;
use App\Clinical\Entities\Models\PerformedProcedure;
use App\Clinical\Entities\Models\Stay;
use App\Hospital\Entities\Models\Room;
use App\Patient\Entities\Models\Patient;
use App\Staff\Entities\Models\Nurse;
use App\Staff\Entities\Models\OnCall;

beforeEach(function () {
    config(['app.hospital_timezone' => 'America/Denver']);
    $this->travelTo('2026-10-16 18:00:00');
});

it('returns 401 to guests', function () {
    $response = $this->getJson(route('api.dashboard.show'));

    $response->assertUnauthorized();
});

it('uses today in hospital time', function (string $now) {
    $this->travelTo($now);

    $response = $this->actingAsLoggedInUser()->getJson(route('api.dashboard.show'));

    $response->assertOk()->assertJson(['date' => '2026-10-16']);
})->with([
    'midday in Denver' => ['2026-10-16 18:00:00'],
    'late evening in Denver, already tomorrow in UTC' => ['2026-10-17 03:00:00'],
]);

it('lists the procedures dated today with the patient phone, physician, nurse and room', function () {
    $patient = Patient::factory()->create(['name' => 'Jordan Sullivan', 'phone' => '(555) 010-0001']);
    $room = Room::factory()->create(['roomID' => 214]);
    $stay = Stay::factory()->for($patient)->for($room)->between('2026-10-14')->create();
    $today = PerformedProcedure::factory()->for($stay)->create(['patient_id' => $patient->id, 'procDate' => '2026-10-16']);
    PerformedProcedure::factory()->for($stay)->create(['patient_id' => $patient->id, 'procDate' => '2026-10-15']);
    PerformedProcedure::factory()->for($stay)->create(['patient_id' => $patient->id, 'procDate' => '2026-10-17']);

    $response = $this->actingAsLoggedInUser()->getJson(route('api.dashboard.show'));

    expect($response->json('procedures.*.id'))->toBe([$today->id]);
    expect($response->json('procedures.0.patient'))->toMatchArray(['id' => $patient->id, 'name' => 'Jordan Sullivan', 'phone' => '(555) 010-0001']);
    expect($response->json('procedures.0.procedure.name'))->toBe($today->procedure->name);
    expect($response->json('procedures.0.stay.room.roomID'))->toBe(214);
    expect($response->json('procedures.0.physician.name'))->toBe($today->physician->name);
    expect($response->json('procedures.0.nurse.name'))->toBe($today->nurse->name);
});

it('lists the appointments starting today in hospital time, earliest first', function () {
    $patient = Patient::factory()->create(['phone' => '(555) 010-0002']);
    $lateEvening = Appointment::factory()->for($patient)->startingAt('2026-10-17 05:00:00')->create();
    $morning = Appointment::factory()->for($patient)->startingAt('2026-10-16 15:00:00')->create();
    $afternoon = Appointment::factory()->for($patient)->startingAt('2026-10-16 22:00:00')->create();
    Appointment::factory()->for($patient)->startingAt('2026-10-16 05:00:00')->create();
    Appointment::factory()->for($patient)->startingAt('2026-10-17 07:00:00')->create();
    Appointment::factory()->for($patient)->startingAt('2026-10-16 20:00:00')->create()->delete();

    $response = $this->actingAsLoggedInUser()->getJson(route('api.dashboard.show'));

    expect($response->json('appointments.*.id'))->toBe([$morning->id, $afternoon->id, $lateEvening->id]);
    expect($response->json('appointments.0.patient.phone'))->toBe('(555) 010-0002');
    expect($response->json('appointments.0'))->toHaveKeys(['startDateTime', 'endDateTime', 'physician.name', 'nurse.name']);
});

it('lists the nurses on call today', function () {
    $coveringToday = OnCall::factory()->between('2026-10-14', '2026-10-16')->create();
    $startingToday = OnCall::factory()->between('2026-10-16', '2026-10-18')->create();
    OnCall::factory()->between('2026-10-12', '2026-10-15')->create();
    OnCall::factory()->between('2026-10-17', '2026-10-19')->create();
    $offboarded = OnCall::factory()->between('2026-10-15', '2026-10-17')->create();
    Nurse::query()->find($offboarded->nurse_id)->delete();

    $response = $this->actingAsLoggedInUser()->getJson(route('api.dashboard.show'));

    expect($response->json('onCallNurses.*.id'))->toEqualCanonicalizing([$coveringToday->id, $startingToday->id]);
    expect($response->json('onCallNurses.0.nurse'))->toHaveKeys(['id', 'nurseID', 'name', 'position']);
    expect($response->json('onCallNurses.0'))->toHaveKeys(['startDate', 'endDate']);
});

it('lists the patients due to be discharged today with their phone and room', function () {
    $patient = Patient::factory()->create(['name' => 'Ben Sullivan', 'phone' => '(555) 010-0003']);
    $room = Room::factory()->create(['roomID' => 108]);
    $dischargedToday = Stay::factory()->for($patient)->for($room)->between('2026-10-12', '2026-10-16')->create();
    Stay::factory()->between('2026-10-10', '2026-10-15')->create();
    Stay::factory()->between('2026-10-14', '2026-10-17')->create();
    Stay::factory()->between('2026-10-14')->create();

    $response = $this->actingAsLoggedInUser()->getJson(route('api.dashboard.show'));

    expect($response->json('discharges.*.id'))->toBe([$dischargedToday->id]);
    expect($response->json('discharges.0'))->toMatchArray(['startDate' => '2026-10-12', 'endDate' => '2026-10-16']);
    expect($response->json('discharges.0.patient'))->toMatchArray(['name' => 'Ben Sullivan', 'phone' => '(555) 010-0003']);
    expect($response->json('discharges.0.room.roomID'))->toBe(108);
});

it('returns empty lists on a quiet day', function () {
    $response = $this->actingAsLoggedInUser()->getJson(route('api.dashboard.show'));

    $response->assertOk()->assertExactJson([
        'date' => '2026-10-16',
        'procedures' => [],
        'appointments' => [],
        'onCallNurses' => [],
        'discharges' => [],
    ]);
});
