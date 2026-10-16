<?php

use App\Clinical\Entities\Models\Procedure;
use Illuminate\Database\UniqueConstraintViolationException;

it('generates a ulid primary key', function () {
    $procedure = Procedure::create(['procID' => 1, 'name' => 'Appendectomy', 'cost' => 1500.5]);

    expect($procedure->getKey())->toBeString()->toHaveLength(26);
});

it('reads the cost back as a float', function () {
    $procedure = Procedure::create(['procID' => 2, 'name' => 'Appendectomy', 'cost' => 1500.5]);

    expect(Procedure::find($procedure->id)->cost)->toBe(1500.5);
});

it('allows a procedure without a cost', function () {
    $procedure = Procedure::create(['procID' => 3, 'name' => 'Appendectomy']);

    expect(Procedure::find($procedure->id)->cost)->toBeNull();
});

it('keeps the row when a procedure is deleted', function () {
    $procedure = Procedure::create(['procID' => 4, 'name' => 'Appendectomy']);

    $procedure->delete();

    $this->assertSoftDeleted($procedure);
});

it('rejects a duplicate procID', function () {
    Procedure::create(['procID' => 1, 'name' => 'Appendectomy']);

    Procedure::create(['procID' => 1, 'name' => 'Appendectomy']);
})->throws(UniqueConstraintViolationException::class);

it('assigns the next procID when none is given', function () {
    Procedure::create(['procID' => 900000, 'name' => 'Appendectomy']);

    $procedure = Procedure::create(['name' => 'Appendectomy']);

    expect($procedure->procID)->toBe(900001);
    expect(Procedure::find($procedure->id)->procID)->toBe(900001);
});
