<?php

use App\Clinical\Entities\Models\Appointment;
use App\Clinical\Entities\Models\PerformedProcedure;
use App\Clinical\Entities\Models\Prescription;
use App\Clinical\Entities\Models\Procedure;
use App\Clinical\Entities\Models\Stay;
use App\Patient\Entities\Models\Patient;

beforeEach(function () {
    $this->travelTo('2026-10-15 12:00:00');
});

function summaryProcedureOf(Patient $patient, string $procDate, ?Procedure $procedure = null): PerformedProcedure
{
    $stay = Stay::factory()->for($patient)->between('2026-10-01')->create();

    return PerformedProcedure::factory()
        ->for($stay)
        ->for($procedure ?? Procedure::factory())
        ->create(['patient_id' => $patient->id, 'procDate' => $procDate]);
}

it('returns 401 to guests', function () {
    $response = $this->getJson(route('api.patients.summary.show', Patient::factory()->create()));

    $response->assertUnauthorized();
});

it('shows the next procedure scheduled for the patient', function () {
    $patient = Patient::factory()->create();
    summaryProcedureOf($patient, '2026-10-15');
    summaryProcedureOf($patient, '2026-11-02');
    $next = summaryProcedureOf($patient, '2026-10-18', Procedure::factory()->create(['name' => 'Knee Arthroscopy']));
    summaryProcedureOf(Patient::factory()->create(), '2026-10-16');

    $response = $this->actingAsLoggedInUser()->getJson(route('api.patients.summary.show', $patient));

    $response->assertOk();
    expect($response->json('nextProcedure'))->toMatchArray(['id' => $next->id, 'procDate' => '2026-10-18']);
    expect($response->json('nextProcedure.procedure'))->toMatchArray(['id' => $next->procedure_id, 'name' => 'Knee Arthroscopy']);
    expect($response->json('nextProcedure.procedure'))->not->toHaveKey('procID');
});

it('has no next procedure when nothing is scheduled', function () {
    $patient = Patient::factory()->create();
    summaryProcedureOf($patient, '2026-10-15');

    $response = $this->actingAsLoggedInUser()->getJson(route('api.patients.summary.show', $patient));

    expect($response->json())->toHaveKey('nextProcedure');
    expect($response->json('nextProcedure'))->toBeNull();
});

it('counts the procedures performed on the patient, leaving out scheduled ones', function () {
    $patient = Patient::factory()->create();
    summaryProcedureOf($patient, '2026-10-02');
    summaryProcedureOf($patient, '2026-10-15');
    summaryProcedureOf($patient, '2026-10-16');
    summaryProcedureOf(Patient::factory()->create(), '2026-10-05');

    $response = $this->actingAsLoggedInUser()->getJson(route('api.patients.summary.show', $patient));

    expect($response->json('performedProceduresCount'))->toBe(2);
});

it('counts the prescriptions issued to the patient', function () {
    $patient = Patient::factory()->create();
    Prescription::factory()->for($patient)->count(3)->create();
    Prescription::factory()->create();

    $response = $this->actingAsLoggedInUser()->getJson(route('api.patients.summary.show', $patient));

    expect($response->json('prescriptionsCount'))->toBe(3);
});

it('totals the revenue from procedures already performed on the patient', function () {
    $patient = Patient::factory()->create();
    $biopsy = Procedure::factory()->create(['cost' => 1200.50]);
    $removedScan = Procedure::factory()->create(['cost' => 300.25]);
    summaryProcedureOf($patient, '2026-10-02', $biopsy);
    summaryProcedureOf($patient, '2026-10-15', $biopsy);
    summaryProcedureOf($patient, '2026-10-10', $removedScan);
    summaryProcedureOf($patient, '2026-10-11', Procedure::factory()->create(['cost' => null]));
    summaryProcedureOf($patient, '2026-10-16', Procedure::factory()->create(['cost' => 9000]));
    summaryProcedureOf(Patient::factory()->create(), '2026-10-05', $biopsy);
    $removedScan->delete();

    $response = $this->actingAsLoggedInUser()->getJson(route('api.patients.summary.show', $patient));

    expect($response->json('totalRevenue'))->toEqual(2701.25);
});

it('shows zeros for a patient with no procedures or prescriptions', function () {
    $patient = Patient::factory()->create();

    $response = $this->actingAsLoggedInUser()->getJson(route('api.patients.summary.show', $patient));

    $response->assertOk()->assertExactJson([
        'nextProcedure' => null,
        'performedProceduresCount' => 0,
        'prescriptionsCount' => 0,
        'totalRevenue' => 0,
        'nextAppointment' => null,
        'completedAppointmentsCount' => 0,
    ]);
});

it('returns 404 for an inactive patient', function () {
    $patient = Patient::factory()->create();
    $patient->delete();

    $response = $this->actingAsLoggedInUser()->getJson(route('api.patients.summary.show', $patient));

    $response->assertNotFound();
});

it('shows the next appointment of the patient', function () {
    $patient = Patient::factory()->create();
    Appointment::factory()->for($patient)->startingAt('2026-10-14 15:00:00')->create();
    Appointment::factory()->for($patient)->startingAt('2026-10-15 11:45:00', minutes: 30)->create();
    Appointment::factory()->for($patient)->startingAt('2026-10-20 15:00:00')->create();
    $next = Appointment::factory()->for($patient)->startingAt('2026-10-16 15:00:00', minutes: 45)->create();
    Appointment::factory()->for($patient)->startingAt('2026-10-15 13:00:00')->create()->delete();
    Appointment::factory()->startingAt('2026-10-15 14:00:00')->create();

    $response = $this->actingAsLoggedInUser()->getJson(route('api.patients.summary.show', $patient));

    expect($response->json('nextAppointment.id'))->toBe($next->id);
    expect($response->json('nextAppointment.startDateTime'))->toStartWith('2026-10-16T15:00:00');
    expect($response->json('nextAppointment.endDateTime'))->toStartWith('2026-10-16T15:45:00');
});

it('counts the completed appointments of the patient, leaving out upcoming, in-progress and cancelled ones', function () {
    $patient = Patient::factory()->create();
    Appointment::factory()->for($patient)->startingAt('2026-09-01 15:00:00')->create();
    Appointment::factory()->for($patient)->startingAt('2026-10-15 11:00:00', minutes: 30)->create();
    Appointment::factory()->for($patient)->startingAt('2026-10-15 11:45:00', minutes: 30)->create();
    Appointment::factory()->for($patient)->startingAt('2026-10-20 15:00:00')->create();
    Appointment::factory()->for($patient)->startingAt('2026-08-01 15:00:00')->create()->delete();
    Appointment::factory()->startingAt('2026-09-02 15:00:00')->create();

    $response = $this->actingAsLoggedInUser()->getJson(route('api.patients.summary.show', $patient));

    expect($response->json('completedAppointmentsCount'))->toBe(2);
});
