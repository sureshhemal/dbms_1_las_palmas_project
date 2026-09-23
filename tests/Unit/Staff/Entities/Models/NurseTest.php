<?php

use App\Staff\Entities\Models\Nurse;
use App\Staff\Entities\NursePosition;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;

it('stores the position as the text allowed by the schema', function () {
    $nurse = Nurse::create(['name' => 'Carla Espinosa', 'position' => NursePosition::HeadNurse]);

    $this->assertDatabaseHas('Nurse', ['id' => $nurse->id, 'position' => 'Head Nurse']);
    expect(Nurse::find($nurse->id)->position)->toBe(NursePosition::HeadNurse);
});

it('generates a ulid primary key', function () {
    $nurse = Nurse::create(['name' => 'Carla Espinosa', 'position' => NursePosition::Nurse]);

    expect($nurse->getKey())->toBeString()->toHaveLength(26);
});

it('rejects a position outside the schema list', function () {
    DB::table('Nurse')->insert(['id' => (string) str()->ulid(), 'name' => 'Carla Espinosa', 'position' => 'Matron']);
})->throws(QueryException::class);

it('keeps the row when a nurse is deleted', function () {
    $nurse = Nurse::create(['name' => 'Carla Espinosa', 'position' => NursePosition::Nurse]);

    $nurse->delete();

    $this->assertSoftDeleted($nurse);
});
