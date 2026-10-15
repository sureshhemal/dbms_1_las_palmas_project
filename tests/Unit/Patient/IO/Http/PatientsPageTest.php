<?php

use Inertia\Testing\AssertableInertia as Assert;

it('redirects guests to the login page', function () {
    $response = $this->get(route('patients'));

    $response->assertRedirect(route('login'));
});

it('renders the patients page for a signed-in user', function () {
    $response = $this->actingAsLoggedInUser()->get(route('patients'));

    $response->assertInertia(fn (Assert $page) => $page->component('Patients'));
});
