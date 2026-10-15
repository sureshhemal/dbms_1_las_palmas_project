<?php

use App\Hospital\Entities\DepartmentName;
use App\Staff\Entities\Models\Physician;

it('returns 401 to guests', function () {
    $response = $this->getJson(route('api.departments.index'));

    $response->assertUnauthorized();
});

it('lists the predefined departments in deptID order', function () {
    $response = $this->actingAsLoggedInUser()->getJson(route('api.departments.index'));

    $response->assertOk()->assertJsonCount(3);
    expect($response->json('*.name'))->toBe(DepartmentName::values());
});

it('includes the head physician of each department when asked', function () {
    $physician = Physician::factory()->create(['name' => 'Bob Kelso']);
    $this->predefinedDepartment(DepartmentName::Surgery)->update(['head_id' => $physician->id]);

    $response = $this->actingAsLoggedInUser()->getJson(route('api.departments.index', ['with_head' => 1]));

    $surgery = collect($response->json())->firstWhere('name', 'Surgery');
    expect($surgery['head']['name'])->toBe('Bob Kelso');
    expect(collect($response->json())->firstWhere('name', 'Psychiatry')['head'])->toBeNull();
});

it('leaves out the head physician unless asked', function () {
    $physician = Physician::factory()->create();
    $this->predefinedDepartment(DepartmentName::Surgery)->update(['head_id' => $physician->id]);

    $response = $this->actingAsLoggedInUser()->getJson(route('api.departments.index'));

    expect(collect($response->json())->firstWhere('name', 'Surgery'))->not->toHaveKey('head');
});

it('leaves out soft-deleted departments', function () {
    $this->predefinedDepartment(DepartmentName::Psychiatry)->delete();

    $response = $this->actingAsLoggedInUser()->getJson(route('api.departments.index'));

    expect($response->json('*.name'))->toBe(['General Medicine', 'Surgery']);
});

it('shows a department with its head physician', function () {
    $physician = Physician::factory()->create(['name' => 'Bob Kelso']);
    $surgery = $this->predefinedDepartment(DepartmentName::Surgery);
    $surgery->update(['head_id' => $physician->id]);

    $response = $this->actingAsLoggedInUser()->getJson(route('api.departments.show', $surgery));

    $response->assertOk()->assertJson(['deptID' => 1002, 'name' => 'Surgery', 'head' => ['name' => 'Bob Kelso']]);
});

it('returns 401 to guests showing a department', function () {
    $response = $this->getJson(route('api.departments.show', $this->predefinedDepartment(DepartmentName::Surgery)));

    $response->assertUnauthorized();
});

it('returns 401 to guests updating a department', function () {
    $surgery = $this->predefinedDepartment(DepartmentName::Surgery);

    $response = $this->putJson(route('api.departments.update', $surgery), ['deptID' => 2002]);

    $response->assertUnauthorized();
    expect($surgery->fresh()->deptID)->toBe(1002);
});

it('updates the department id and head', function () {
    $physician = Physician::factory()->create(['name' => 'Chris Turk']);
    $surgery = $this->predefinedDepartment(DepartmentName::Surgery);

    $response = $this->actingAsLoggedInUser()->putJson(route('api.departments.update', $surgery), [
        'deptID' => 2002,
        'head_id' => $physician->id,
    ]);

    $response->assertOk()->assertJson(['deptID' => 2002, 'head' => ['name' => 'Chris Turk']]);
    $this->assertDatabaseHas('Department', ['id' => $surgery->id, 'deptID' => 2002, 'head_id' => $physician->id]);
});

it('clears the head when it is left blank', function () {
    $physician = Physician::factory()->create();
    $surgery = $this->predefinedDepartment(DepartmentName::Surgery);
    $surgery->update(['head_id' => $physician->id]);

    $response = $this->actingAsLoggedInUser()->putJson(route('api.departments.update', $surgery), [
        'deptID' => 1002,
        'head_id' => '',
    ]);

    $response->assertOk();
    $this->assertDatabaseHas('Department', ['id' => $surgery->id, 'deptID' => 1002, 'head_id' => null]);
});

it('shows the MySQL error when the department id is left blank', function () {
    config(['app.debug' => true]);
    $surgery = $this->predefinedDepartment(DepartmentName::Surgery);

    $response = $this->actingAsLoggedInUser()->putJson(route('api.departments.update', $surgery), [
        'deptID' => '',
    ]);

    $response->assertInternalServerError();
    expect($response->json('message'))->toContain("Column 'deptID' cannot be null");
    expect($surgery->fresh()->deptID)->toBe(1002);
});

it('never changes the department name', function () {
    $surgery = $this->predefinedDepartment(DepartmentName::Surgery);

    $this->actingAsLoggedInUser()->putJson(route('api.departments.update', $surgery), [
        'name' => 'Psychiatry',
        'deptID' => 1002,
    ]);

    expect($surgery->fresh()->name)->toBe(DepartmentName::Surgery);
});

it('shows the MySQL error when the department id is already taken', function () {
    config(['app.debug' => true]);
    $surgery = $this->predefinedDepartment(DepartmentName::Surgery);

    $response = $this->actingAsLoggedInUser()->putJson(route('api.departments.update', $surgery), [
        'deptID' => 1001,
    ]);

    $response->assertInternalServerError();
    expect($response->json('message'))->toContain("Duplicate entry '1001'");
    expect($surgery->fresh()->deptID)->toBe(1002);
});

it('shows the MySQL error when the head is not a physician', function () {
    config(['app.debug' => true]);
    $surgery = $this->predefinedDepartment(DepartmentName::Surgery);

    $response = $this->actingAsLoggedInUser()->putJson(route('api.departments.update', $surgery), [
        'deptID' => 1002,
        'head_id' => (string) str()->ulid(),
    ]);

    $response->assertInternalServerError();
    expect($response->json('message'))->toContain('foreign key constraint fails');
});
