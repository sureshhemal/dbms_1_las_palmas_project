<?php

use App\IAM\Entities\User;
use Inertia\Testing\AssertableInertia as Assert;

it('redirects guests to the login page', function () {
    $response = $this->get(route('staff'));

    $response->assertRedirect(route('login'));
});

it('renders the staff page for a signed-in user', function () {
    $user = User::factory()->unverified()->create();

    $response = $this->actingAs($user)->get(route('staff'));

    $response->assertInertia(fn (Assert $page) => $page->component('Staff'));
});
