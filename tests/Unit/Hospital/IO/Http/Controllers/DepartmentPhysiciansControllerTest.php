<?php

use App\Hospital\Entities\DepartmentName;
use App\Staff\Entities\Models\Physician;

it('returns 401 to guests', function () {
    $surgery = $this->predefinedDepartment(DepartmentName::Surgery);

    $response = $this->getJson(route('api.departments.physicians.index', $surgery));

    $response->assertUnauthorized();
});

it('lists the physicians of the department with their join date, earliest first', function () {
    $surgery = $this->predefinedDepartment(DepartmentName::Surgery);
    $elliot = Physician::factory()->create(['name' => 'Elliot Reid']);
    $turk = Physician::factory()->create(['name' => 'Chris Turk']);

    $this->travelTo('2026-03-01 09:00:00');
    $turk->departments()->attach($surgery);
    $this->travelTo('2026-05-01 09:00:00');
    $elliot->departments()->attach($surgery);

    $response = $this->actingAsLoggedInUser()->getJson(route('api.departments.physicians.index', $surgery));

    $response->assertOk()->assertJsonCount(2);
    expect($response->json('*.name'))->toBe(['Chris Turk', 'Elliot Reid']);
    expect($response->json('0.joined_at'))->toBe('2026-03-01T09:00:00.000000Z');
    expect($response->json('0'))->not->toHaveKey('pivot');
});

it('leaves out physicians of other departments and offboarded physicians', function () {
    $surgery = $this->predefinedDepartment(DepartmentName::Surgery);
    Physician::factory()->create(['name' => 'Chris Turk'])->departments()->attach($surgery);
    Physician::factory()->create()->departments()->attach($this->predefinedDepartment(DepartmentName::Psychiatry));
    $offboarded = Physician::factory()->create();
    $offboarded->departments()->attach($surgery);
    $offboarded->delete();

    $response = $this->actingAsLoggedInUser()->getJson(route('api.departments.physicians.index', $surgery));

    expect($response->json('*.name'))->toBe(['Chris Turk']);
});
