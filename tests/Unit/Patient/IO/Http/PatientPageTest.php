<?php

use App\Patient\Entities\Models\Patient;
use Inertia\Testing\AssertableInertia as Assert;

it('redirects guests to the login page', function () {
    $response = $this->get(route('patients.show', Patient::factory()->create()));

    $response->assertRedirect(route('login'));
});

it('renders the patient page with the patient id', function () {
    $patient = Patient::factory()->create();

    $response = $this->actingAsLoggedInUser()->get(route('patients.show', $patient));

    $response->assertInertia(fn (Assert $page) => $page
        ->component('Patient')
        ->where('patientId', $patient->id));
});

it('returns 404 for an inactive patient', function () {
    $patient = Patient::factory()->create();
    $patient->delete();

    $response = $this->actingAsLoggedInUser()->get(route('patients.show', $patient));

    $response->assertNotFound();
});
