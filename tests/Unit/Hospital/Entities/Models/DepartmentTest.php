<?php

use App\Hospital\Entities\DepartmentName;
use App\Hospital\Entities\Models\Department;
use App\Staff\Entities\Models\Physician;
use App\Staff\Entities\PhysicianPosition;
use Illuminate\Database\QueryException;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Support\Facades\DB;

it('comes with the three predefined departments', function () {
    expect(DB::table('Department')->pluck('name')->all())->toEqualCanonicalizing(DepartmentName::values());
    expect(Department::pluck('name')->all())->toEqualCanonicalizing(DepartmentName::cases());
});

it('gives each predefined department a ulid primary key and a four-digit deptID', function () {
    $departments = Department::query()->orderBy('deptID')->get();

    expect($departments->pluck('id'))->each->toBeString()->toHaveLength(26);
    expect($departments->pluck('deptID', 'name.value')->all())->toBe([
        'General Medicine' => 1001,
        'Surgery' => 1002,
        'Psychiatry' => 1003,
    ]);
});

it('rejects a name outside the schema list', function () {
    DB::table('Department')->where('name', 'Surgery')->update(['name' => 'Cardiology']);
})->throws(QueryException::class);

it('returns the head physician', function () {
    $physician = Physician::create(['physicianID' => 1, 'name' => 'Perry Cox', 'position' => PhysicianPosition::Senior]);
    $generalMedicine = $this->predefinedDepartment(DepartmentName::GeneralMedicine);

    $generalMedicine->update(['head_id' => $physician->id]);

    expect($generalMedicine->head->is($physician))->toBeTrue();
});

it('rejects a head that is not a physician', function () {
    $this->predefinedDepartment(DepartmentName::Surgery)->update(['head_id' => (string) str()->ulid()]);
})->throws(QueryException::class);

it('clears the head when the head physician is permanently deleted', function () {
    $physician = Physician::create(['physicianID' => 2, 'name' => 'Perry Cox', 'position' => PhysicianPosition::Senior]);
    $generalMedicine = $this->predefinedDepartment(DepartmentName::GeneralMedicine);
    $generalMedicine->update(['head_id' => $physician->id]);

    $physician->forceDelete();

    expect($generalMedicine->fresh()->head_id)->toBeNull();
});

it('lists the physicians affiliated with the department', function () {
    Physician::create(['physicianID' => 3, 'name' => 'John Dorian', 'position' => PhysicianPosition::Resident]);
    $turk = Physician::create(['physicianID' => 4, 'name' => 'Chris Turk', 'position' => PhysicianPosition::Surgeon]);
    $surgery = $this->predefinedDepartment(DepartmentName::Surgery);

    $surgery->physicians()->attach($turk);

    expect($surgery->physicians->pluck('id')->all())->toBe([$turk->id]);
});

it('keeps the row when a department is deleted', function () {
    $surgery = $this->predefinedDepartment(DepartmentName::Surgery);

    $surgery->delete();

    $this->assertSoftDeleted($surgery);
});

it('rejects a duplicate deptID', function () {
    $generalMedicine = $this->predefinedDepartment(DepartmentName::GeneralMedicine);

    $this->predefinedDepartment(DepartmentName::Surgery)->update(['deptID' => $generalMedicine->deptID]);
})->throws(UniqueConstraintViolationException::class);
