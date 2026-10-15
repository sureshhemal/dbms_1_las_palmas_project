<?php

use App\Hospital\Entities\DepartmentName;
use App\Hospital\Entities\Models\Department;
use App\Staff\Entities\Models\AffiliatedWith;
use App\Staff\Entities\Models\Physician;
use App\Staff\Entities\PhysicianPosition;
use Illuminate\Database\QueryException;

it('deletes only the affiliation it was loaded for', function () {
    $physician = Physician::create(['physicianID' => 1, 'name' => 'John Dorian', 'position' => PhysicianPosition::Resident]);
    $surgery = Department::create(['deptID' => 1, 'name' => DepartmentName::Surgery]);
    $psychiatry = Department::create(['deptID' => 5, 'name' => DepartmentName::Psychiatry]);
    $physician->departments()->attach([$surgery->id, $psychiatry->id]);

    AffiliatedWith::where('department_id', $surgery->id)->first()->delete();

    expect($physician->departments->pluck('id')->all())->toBe([$psychiatry->id]);
});

it('rejects affiliating a physician with the same department twice', function () {
    $physician = Physician::create(['physicianID' => 2, 'name' => 'John Dorian', 'position' => PhysicianPosition::Resident]);
    $department = Department::create(['deptID' => 2, 'name' => DepartmentName::Surgery]);
    $physician->departments()->attach($department);

    $physician->departments()->attach($department);
})->throws(QueryException::class);

it('leaves a soft-deleted affiliation out of the departments', function () {
    $physician = Physician::create(['physicianID' => 3, 'name' => 'John Dorian', 'position' => PhysicianPosition::Resident]);
    $department = Department::create(['deptID' => 3, 'name' => DepartmentName::Surgery]);
    $physician->departments()->attach($department);

    AffiliatedWith::first()->delete();

    expect($physician->departments)->toBeEmpty();
    $this->assertSoftDeleted('AffiliatedWith', ['physician_id' => $physician->id, 'department_id' => $department->id]);
});

it('removes the affiliations when the physician is permanently deleted', function () {
    $physician = Physician::create(['physicianID' => 4, 'name' => 'John Dorian', 'position' => PhysicianPosition::Resident]);
    $department = Department::create(['deptID' => 4, 'name' => DepartmentName::Surgery]);
    $physician->departments()->attach($department);

    $physician->forceDelete();

    $this->assertDatabaseEmpty('AffiliatedWith');
});
