<?php

use App\Hospital\Entities\DepartmentName;
use App\Hospital\Entities\Models\Department;
use App\Staff\Entities\Models\Physician;
use App\Staff\Entities\PhysicianPosition;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;

it('stores the position as the text allowed by the schema', function () {
    $physician = Physician::create(['name' => 'Perry Cox', 'position' => PhysicianPosition::ChiefOfMedicine]);

    $this->assertDatabaseHas('Physician', ['id' => $physician->id, 'position' => 'Chief of Medicine']);
    expect(Physician::find($physician->id)->position)->toBe(PhysicianPosition::ChiefOfMedicine);
});

it('generates a ulid primary key', function () {
    $physician = Physician::create(['name' => 'Perry Cox', 'position' => PhysicianPosition::Senior]);

    expect($physician->getKey())->toBeString()->toHaveLength(26);
});

it('rejects a position outside the schema list', function () {
    DB::table('Physician')->insert(['id' => (string) str()->ulid(), 'name' => 'Perry Cox', 'position' => 'Janitor']);
})->throws(QueryException::class);

it('lists the departments the physician is affiliated with', function () {
    $physician = Physician::create(['name' => 'John Dorian', 'position' => PhysicianPosition::Resident]);
    $surgery = Department::create(['name' => DepartmentName::Surgery]);
    Department::create(['name' => DepartmentName::Psychiatry]);

    $physician->departments()->attach($surgery);

    expect($physician->departments->pluck('id')->all())->toBe([$surgery->id]);
});

it('keeps the row when a physician is deleted', function () {
    $physician = Physician::create(['name' => 'Perry Cox', 'position' => PhysicianPosition::Senior]);

    $physician->delete();

    $this->assertSoftDeleted($physician);
});
