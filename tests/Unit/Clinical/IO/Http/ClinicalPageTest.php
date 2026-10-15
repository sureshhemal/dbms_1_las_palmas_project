<?php

use Inertia\Testing\AssertableInertia as Assert;

it('redirects guests to the login page', function () {
    $response = $this->get(route('clinical'));

    $response->assertRedirect(route('login'));
});

it('renders the clinical page for a signed-in user', function () {
    $response = $this->actingAsLoggedInUser()->get(route('clinical'));

    $response->assertInertia(fn (Assert $page) => $page->component('Clinical'));
});
