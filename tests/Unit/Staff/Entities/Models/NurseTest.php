<?php

use App\Staff\Entities\Models\Nurse;
use App\Staff\Entities\NursePosition;
use Illuminate\Database\QueryException;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Support\Facades\DB;

it('stores the position as the text allowed by the schema', function () {
    $nurse = Nurse::create(['nurseID' => 1, 'name' => 'Carla Espinosa', 'position' => NursePosition::HeadNurse]);

    $this->assertDatabaseHas('Nurse', ['id' => $nurse->id, 'position' => 'Head Nurse']);
    expect(Nurse::find($nurse->id)->position)->toBe(NursePosition::HeadNurse);
});

it('generates a ulid primary key', function () {
    $nurse = Nurse::create(['nurseID' => 2, 'name' => 'Carla Espinosa', 'position' => NursePosition::Nurse]);

    expect($nurse->getKey())->toBeString()->toHaveLength(26);
});

it('rejects a position outside the schema list', function () {
    DB::table('Nurse')->insert(['nurseID' => 3, 'id' => (string) str()->ulid(), 'name' => 'Carla Espinosa', 'position' => 'Matron']);
})->throws(QueryException::class);

it('keeps the row when a nurse is deleted', function () {
    $nurse = Nurse::create(['nurseID' => 4, 'name' => 'Carla Espinosa', 'position' => NursePosition::Nurse]);

    $nurse->delete();

    $this->assertSoftDeleted($nurse);
});

it('rejects a duplicate nurseID', function () {
    Nurse::create(['nurseID' => 1, 'name' => 'Carla Espinosa', 'position' => NursePosition::Nurse]);

    Nurse::create(['nurseID' => 1, 'name' => 'Carla Espinosa', 'position' => NursePosition::Nurse]);
})->throws(UniqueConstraintViolationException::class);

it('assigns the next nurseID when none is given', function () {
    Nurse::create(['nurseID' => 1000, 'name' => 'Carla Espinosa', 'position' => NursePosition::Nurse]);

    $nurse = Nurse::create(['name' => 'Carla Espinosa', 'position' => NursePosition::Nurse]);

    expect($nurse->nurseID)->toBe(1001);
    expect(Nurse::find($nurse->id)->nurseID)->toBe(1001);
});

it('keeps the leading zeros of the ssn', function () {
    $nurse = Nurse::create(['nurseID' => 1, 'name' => 'Carla Espinosa', 'position' => NursePosition::Nurse, 'ssn' => '012-34-5678']);

    expect(Nurse::find($nurse->id)->ssn)->toBe('012-34-5678');
});

it('creates valid nurses from the factory', function () {
    Nurse::factory()->count(3)->create();

    $this->assertDatabaseCount('Nurse', 3);
});
