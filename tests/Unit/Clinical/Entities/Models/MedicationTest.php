<?php

use App\Clinical\Entities\Models\Medication;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;

it('generates a ulid primary key', function () {
    $medication = Medication::create(['name' => 'Amoxicillin']);

    expect($medication->getKey())->toBeString()->toHaveLength(26);
});

it('rejects a medication without a name', function () {
    DB::table('Medication')->insert(['id' => (string) str()->ulid()]);
})->throws(QueryException::class);

it('keeps the row when a medication is deleted', function () {
    $medication = Medication::create(['name' => 'Amoxicillin']);

    $medication->delete();

    $this->assertSoftDeleted($medication);
});
