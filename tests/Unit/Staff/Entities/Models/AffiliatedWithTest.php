<?php

use App\Hospital\Entities\DepartmentName;
use App\Hospital\Entities\Models\Department;
use App\Staff\Entities\Models\AffiliatedWith;
use App\Staff\Entities\Models\Physician;
use App\Staff\Entities\PhysicianPosition;
use Illuminate\Database\QueryException;

it('gives each affiliation a ulid primary key', function () {
    $physician = Physician::create(['name' => 'John Dorian', 'position' => PhysicianPosition::Resident]);
    $department = Department::create(['name' => DepartmentName::Surgery]);

    $physician->departments()->attach($department);

    $affiliation = AffiliatedWith::first();
    expect($affiliation->id)->toBeString()->toHaveLength(26);
    expect($affiliation->physician_id)->toBe($physician->id);
    expect($affiliation->department_id)->toBe($department->id);
});

it('rejects affiliating a physician with the same department twice', function () {
    $physician = Physician::create(['name' => 'John Dorian', 'position' => PhysicianPosition::Resident]);
    $department = Department::create(['name' => DepartmentName::Surgery]);
    $physician->departments()->attach($department);

    $physician->departments()->attach($department);
})->throws(QueryException::class);

it('leaves a soft-deleted affiliation out of the departments', function () {
    $physician = Physician::create(['name' => 'John Dorian', 'position' => PhysicianPosition::Resident]);
    $department = Department::create(['name' => DepartmentName::Surgery]);
    $physician->departments()->attach($department);

    AffiliatedWith::first()->delete();

    expect($physician->departments)->toBeEmpty();
    $this->assertSoftDeleted('AffiliatedWith', ['physician_id' => $physician->id, 'department_id' => $department->id]);
});

it('removes the affiliations when the physician is permanently deleted', function () {
    $physician = Physician::create(['name' => 'John Dorian', 'position' => PhysicianPosition::Resident]);
    $department = Department::create(['name' => DepartmentName::Surgery]);
    $physician->departments()->attach($department);

    $physician->forceDelete();

    $this->assertDatabaseEmpty('AffiliatedWith');
});
