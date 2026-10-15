<?php

use App\Hospital\Entities\DepartmentName;
use App\Hospital\Entities\Models\Department;
use App\Staff\Entities\Models\Physician;
use App\Staff\Entities\PhysicianPosition;
use Illuminate\Database\QueryException;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Support\Facades\DB;

it('stores the name as the text allowed by the schema', function () {
    $department = Department::create(['deptID' => 1, 'name' => DepartmentName::GeneralMedicine]);

    $this->assertDatabaseHas('Department', ['id' => $department->id, 'name' => 'General Medicine']);
    expect(Department::find($department->id)->name)->toBe(DepartmentName::GeneralMedicine);
});

it('generates a ulid primary key', function () {
    $department = Department::create(['deptID' => 2, 'name' => DepartmentName::Surgery]);

    expect($department->getKey())->toBeString()->toHaveLength(26);
});

it('rejects a name outside the schema list', function () {
    DB::table('Department')->insert(['deptID' => 3, 'id' => (string) str()->ulid(), 'name' => 'Cardiology']);
})->throws(QueryException::class);

it('returns the head physician', function () {
    $physician = Physician::create(['physicianID' => 1, 'name' => 'Perry Cox', 'position' => PhysicianPosition::Senior]);
    $department = Department::create(['deptID' => 4, 'name' => DepartmentName::GeneralMedicine, 'head_id' => $physician->id]);

    expect($department->head->is($physician))->toBeTrue();
});

it('rejects a head that is not a physician', function () {
    Department::create(['deptID' => 5, 'name' => DepartmentName::Surgery, 'head_id' => (string) str()->ulid()]);
})->throws(QueryException::class);

it('clears the head when the head physician is permanently deleted', function () {
    $physician = Physician::create(['physicianID' => 2, 'name' => 'Perry Cox', 'position' => PhysicianPosition::Senior]);
    $department = Department::create(['deptID' => 6, 'name' => DepartmentName::GeneralMedicine, 'head_id' => $physician->id]);

    $physician->forceDelete();

    expect($department->fresh()->head_id)->toBeNull();
});

it('lists the physicians affiliated with the department', function () {
    Physician::create(['physicianID' => 3, 'name' => 'John Dorian', 'position' => PhysicianPosition::Resident]);
    $turk = Physician::create(['physicianID' => 4, 'name' => 'Chris Turk', 'position' => PhysicianPosition::Surgeon]);
    $department = Department::create(['deptID' => 7, 'name' => DepartmentName::Surgery]);

    $department->physicians()->attach($turk);

    expect($department->physicians->pluck('id')->all())->toBe([$turk->id]);
});

it('keeps the row when a department is deleted', function () {
    $department = Department::create(['deptID' => 8, 'name' => DepartmentName::Surgery]);

    $department->delete();

    $this->assertSoftDeleted($department);
});

it('rejects a duplicate deptID', function () {
    Department::create(['deptID' => 1, 'name' => DepartmentName::Surgery]);

    Department::create(['deptID' => 1, 'name' => DepartmentName::Surgery]);
})->throws(UniqueConstraintViolationException::class);

it('assigns the next deptID when none is given', function () {
    Department::create(['deptID' => 1000, 'name' => DepartmentName::Surgery]);

    $department = Department::create(['name' => DepartmentName::Surgery]);

    expect($department->deptID)->toBe(1001);
    expect(Department::find($department->id)->deptID)->toBe(1001);
});
