<?php

use App\Clinical\Entities\Models\Procedure;

it('generates a ulid primary key', function () {
    $procedure = Procedure::create(['name' => 'Appendectomy', 'cost' => 1500.5]);

    expect($procedure->getKey())->toBeString()->toHaveLength(26);
});

it('reads the cost back as a float', function () {
    $procedure = Procedure::create(['name' => 'Appendectomy', 'cost' => 1500.5]);

    expect(Procedure::find($procedure->id)->cost)->toBe(1500.5);
});

it('allows a procedure without a cost', function () {
    $procedure = Procedure::create(['name' => 'Appendectomy']);

    expect(Procedure::find($procedure->id)->cost)->toBeNull();
});

it('keeps the row when a procedure is deleted', function () {
    $procedure = Procedure::create(['name' => 'Appendectomy']);

    $procedure->delete();

    $this->assertSoftDeleted($procedure);
});
