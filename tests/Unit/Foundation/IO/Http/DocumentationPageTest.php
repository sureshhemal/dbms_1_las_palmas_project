<?php

use Inertia\Testing\AssertableInertia as Assert;

it('shows the documentation to guests without signing in', function () {
    $response = $this->get(route('documentation'));

    $response->assertOk()->assertInertia(fn (Assert $page) => $page
        ->component('Documentation')
        ->where('html', fn (string $html): bool => str_contains($html, 'Las Palmas Medical Center')));
});

it('renders the readme as html', function () {
    $response = $this->actingAsLoggedInUser()->get(route('documentation'));

    $response->assertInertia(fn (Assert $page) => $page
        ->component('Documentation')
        ->where('html', fn (string $html): bool => str_contains($html, '<h1')
            && str_contains($html, 'Las Palmas Medical Center')
            && str_contains($html, '<table>')
            && str_contains($html, '<pre><code')));
});

it('gives headings the same ids as github so the contents links work', function () {
    $response = $this->actingAsLoggedInUser()->get(route('documentation'));

    $response->assertInertia(fn (Assert $page) => $page
        ->where('html', fn (string $html): bool => str_contains($html, 'id="1-what-you-need"')
            && str_contains($html, 'href="#1-what-you-need"')
            && str_contains($html, 'id="15-report-percentage-shares-with-avg-versus-what-the-app-uses"')));
});

it('opens external links in a new tab', function () {
    $response = $this->actingAsLoggedInUser()->get(route('documentation'));

    $response->assertInertia(fn (Assert $page) => $page
        ->where('html', fn (string $html): bool => (bool) preg_match('/<a(?=[^>]*href="https:\/\/vuejs\.org")(?=[^>]*target="_blank")[^>]*>/', $html)));
});
