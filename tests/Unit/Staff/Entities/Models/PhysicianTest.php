<?php

use App\Hospital\Entities\DepartmentName;
use App\Hospital\Entities\Models\Department;
use App\Staff\Entities\Models\Physician;
use App\Staff\Entities\PhysicianPosition;
use Illuminate\Database\QueryException;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Support\Facades\DB;

it('stores the position as the text allowed by the schema', function () {
    $physician = Physician::create(['physicianID' => 1, 'name' => 'Perry Cox', 'position' => PhysicianPosition::ChiefOfMedicine]);

    $this->assertDatabaseHas('Physician', ['id' => $physician->id, 'position' => 'Chief of Medicine']);
    expect(Physician::find($physician->id)->position)->toBe(PhysicianPosition::ChiefOfMedicine);
});

it('generates a ulid primary key', function () {
    $physician = Physician::create(['physicianID' => 2, 'name' => 'Perry Cox', 'position' => PhysicianPosition::Senior]);

    expect($physician->getKey())->toBeString()->toHaveLength(26);
});

it('rejects a position outside the schema list', function () {
    DB::table('Physician')->insert(['physicianID' => 3, 'id' => (string) str()->ulid(), 'name' => 'Perry Cox', 'position' => 'Janitor']);
})->throws(QueryException::class);

it('lists the departments the physician is affiliated with', function () {
    $physician = Physician::create(['physicianID' => 4, 'name' => 'John Dorian', 'position' => PhysicianPosition::Resident]);
    $surgery = Department::create(['deptID' => 1, 'name' => DepartmentName::Surgery]);
    Department::create(['deptID' => 2, 'name' => DepartmentName::Psychiatry]);

    $physician->departments()->attach($surgery);

    expect($physician->departments->pluck('id')->all())->toBe([$surgery->id]);
});

it('keeps the row when a physician is deleted', function () {
    $physician = Physician::create(['physicianID' => 5, 'name' => 'Perry Cox', 'position' => PhysicianPosition::Senior]);

    $physician->delete();

    $this->assertSoftDeleted($physician);
});

it('rejects a duplicate physicianID', function () {
    Physician::create(['physicianID' => 1, 'name' => 'Perry Cox', 'position' => PhysicianPosition::Senior]);

    Physician::create(['physicianID' => 1, 'name' => 'Perry Cox', 'position' => PhysicianPosition::Senior]);
})->throws(UniqueConstraintViolationException::class);

it('requires a physicianID', function () {
    Physician::create(['name' => 'Perry Cox', 'position' => PhysicianPosition::Senior]);
})->throws(QueryException::class, "Field 'physicianID' doesn't have a default value");

it('keeps the leading zeros of the ssn', function () {
    $physician = Physician::create(['physicianID' => 1, 'name' => 'Perry Cox', 'position' => PhysicianPosition::Senior, 'ssn' => '012-34-5678']);

    expect(Physician::find($physician->id)->ssn)->toBe('012-34-5678');
});
