<?php

use App\Hospital\Entities\DepartmentName;
use Inertia\Testing\AssertableInertia as Assert;

it('redirects guests to the login page', function () {
    $surgery = $this->predefinedDepartment(DepartmentName::Surgery);

    $response = $this->get(route('hospital.departments.show', $surgery));

    $response->assertRedirect(route('login'));
});

it('renders the department page with the department id', function () {
    $surgery = $this->predefinedDepartment(DepartmentName::Surgery);

    $response = $this->actingAsLoggedInUser()->get(route('hospital.departments.show', $surgery));

    $response->assertInertia(fn (Assert $page) => $page
        ->component('Department')
        ->where('departmentId', $surgery->id));
});

it('returns 404 for a soft-deleted department', function () {
    $psychiatry = $this->predefinedDepartment(DepartmentName::Psychiatry);
    $psychiatry->delete();

    $response = $this->actingAsLoggedInUser()->get(route('hospital.departments.show', $psychiatry));

    $response->assertNotFound();
});
