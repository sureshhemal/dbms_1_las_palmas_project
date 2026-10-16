<?php

use App\Clinical\Entities\Models\Medication;
use App\Clinical\Entities\Models\Prescription;
use App\Patient\Entities\Models\Patient;

it('returns 401 to guests', function () {
    $response = $this->getJson(route('api.patients.medications.index', Patient::factory()->create()));

    $response->assertUnauthorized();
});

it('groups the prescriptions of the patient by medication with their share, most prescribed first', function () {
    $patient = Patient::factory()->create();
    $ibuprofen = Medication::factory()->create(['name' => 'Ibuprofen']);
    $amoxicillin = Medication::factory()->create(['name' => 'Amoxicillin']);
    $metformin = Medication::factory()->create(['name' => 'Metformin']);
    Prescription::factory()->for($patient)->for($ibuprofen)->count(2)->create();
    Prescription::factory()->for($patient)->for($metformin)->create();
    Prescription::factory()->for($patient)->for($amoxicillin)->create();
    Prescription::factory()->for($ibuprofen)->count(5)->create();

    $response = $this->actingAsLoggedInUser()->getJson(route('api.patients.medications.index', $patient));

    $response->assertOk()->assertExactJson([
        ['medicationId' => $ibuprofen->id, 'name' => 'Ibuprofen', 'isRemoved' => false, 'prescriptionsCount' => 2, 'percentage' => 50],
        ['medicationId' => $amoxicillin->id, 'name' => 'Amoxicillin', 'isRemoved' => false, 'prescriptionsCount' => 1, 'percentage' => 25],
        ['medicationId' => $metformin->id, 'name' => 'Metformin', 'isRemoved' => false, 'prescriptionsCount' => 1, 'percentage' => 25],
    ]);
});

it('rounds each share to one decimal place', function () {
    $patient = Patient::factory()->create();
    Prescription::factory()->for($patient)->for(Medication::factory()->create(['name' => 'Ibuprofen']))->count(2)->create();
    Prescription::factory()->for($patient)->for(Medication::factory()->create(['name' => 'Metformin']))->create();

    $response = $this->actingAsLoggedInUser()->getJson(route('api.patients.medications.index', $patient));

    expect($response->json('*.percentage'))->toEqual([66.7, 33.3]);
});

it('still lists a medication that has since been removed', function () {
    $patient = Patient::factory()->create();
    $removed = Medication::factory()->create(['name' => 'Ranitidine']);
    Prescription::factory()->for($patient)->for($removed)->create();
    $removed->delete();

    $response = $this->actingAsLoggedInUser()->getJson(route('api.patients.medications.index', $patient));

    expect($response->json('0'))->toMatchArray(['name' => 'Ranitidine', 'isRemoved' => true, 'prescriptionsCount' => 1]);
});

it('returns an empty list for a patient without prescriptions', function () {
    $response = $this->actingAsLoggedInUser()->getJson(route('api.patients.medications.index', Patient::factory()->create()));

    $response->assertOk()->assertExactJson([]);
});

it('returns 404 for an inactive patient', function () {
    $patient = Patient::factory()->create();
    $patient->delete();

    $response = $this->actingAsLoggedInUser()->getJson(route('api.patients.medications.index', $patient));

    $response->assertNotFound();
});
