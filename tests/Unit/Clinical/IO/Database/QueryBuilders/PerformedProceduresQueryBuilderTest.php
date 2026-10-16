<?php

use App\Clinical\Entities\Models\PerformedProcedure;
use App\Clinical\Entities\PerformedProcedureStatus;

beforeEach(function () {
    $this->travelTo('2026-10-15 12:00:00');

    $this->yesterday = PerformedProcedure::factory()->performedOn('2026-10-14')->create();
    $this->today = PerformedProcedure::factory()->performedOn('2026-10-15')->create();
    $this->tomorrow = PerformedProcedure::factory()->performedOn('2026-10-16')->create();
});

it('finds the procedures performed up to today', function () {
    expect(PerformedProcedure::query()->wherePerformed()->pluck('id')->all())
        ->toEqualCanonicalizing([$this->yesterday->id, $this->today->id]);
});

it('finds the procedures scheduled after today', function () {
    expect(PerformedProcedure::query()->whereScheduled()->pluck('id')->all())->toBe([$this->tomorrow->id]);
});

it('combines the chosen statuses', function () {
    expect(PerformedProcedure::query()->whereStatusIn([PerformedProcedureStatus::Performed, PerformedProcedureStatus::Scheduled])->count())->toBe(3);
});
