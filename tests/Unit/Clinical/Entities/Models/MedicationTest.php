<?php

use App\Clinical\Entities\Models\Medication;
use Illuminate\Database\QueryException;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Support\Facades\DB;

it('generates a ulid primary key', function () {
    $medication = Medication::create(['medID' => 1, 'name' => 'Amoxicillin']);

    expect($medication->getKey())->toBeString()->toHaveLength(26);
});

it('rejects a medication without a name', function () {
    DB::table('Medication')->insert(['medID' => 2, 'id' => (string) str()->ulid()]);
})->throws(QueryException::class);

it('keeps the row when a medication is deleted', function () {
    $medication = Medication::create(['medID' => 3, 'name' => 'Amoxicillin']);

    $medication->delete();

    $this->assertSoftDeleted($medication);
});

it('rejects a duplicate medID', function () {
    Medication::create(['medID' => 1, 'name' => 'Amoxicillin']);

    Medication::create(['medID' => 1, 'name' => 'Amoxicillin']);
})->throws(UniqueConstraintViolationException::class);

it('assigns the next medID when none is given', function () {
    Medication::create(['medID' => 900000, 'name' => 'Amoxicillin']);

    $medication = Medication::create(['name' => 'Amoxicillin']);

    expect($medication->medID)->toBe(900001);
    expect(Medication::find($medication->id)->medID)->toBe(900001);
});
