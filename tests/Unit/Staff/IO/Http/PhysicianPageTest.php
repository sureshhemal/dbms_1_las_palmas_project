<?php

use App\Staff\Entities\Models\Physician;
use Inertia\Testing\AssertableInertia as Assert;

it('redirects guests to the login page', function () {
    $response = $this->get(route('staff.physicians.show', Physician::factory()->create()));

    $response->assertRedirect(route('login'));
});

it('renders the physician page with the physician id', function () {
    $physician = Physician::factory()->create();

    $response = $this->actingAsLoggedInUser()->get(route('staff.physicians.show', $physician));

    $response->assertInertia(fn (Assert $page) => $page
        ->component('Physician')
        ->where('physicianId', $physician->id));
});

it('returns 404 for an offboarded physician', function () {
    $physician = Physician::factory()->create();
    $physician->delete();

    $response = $this->actingAsLoggedInUser()->get(route('staff.physicians.show', $physician));

    $response->assertNotFound();
});
