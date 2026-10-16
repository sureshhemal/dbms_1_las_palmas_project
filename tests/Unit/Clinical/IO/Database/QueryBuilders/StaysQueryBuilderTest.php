<?php

use App\Clinical\Entities\Models\Stay;
use App\Clinical\Entities\StayStatus;

beforeEach(function () {
    $this->travelTo('2026-10-15 12:00:00');

    $this->stays = [
        'open' => Stay::factory()->between('2026-10-12')->create(),
        'planned to end later' => Stay::factory()->between('2026-10-12', '2026-10-20')->create(),
        'ending today' => Stay::factory()->between('2026-10-10', '2026-10-15')->create(),
        'starting tomorrow' => Stay::factory()->between('2026-10-16', '2026-10-19')->create(),
        'over' => Stay::factory()->between('2026-10-01', '2026-10-04')->create(),
    ];
});

function stayNames(array $stays, iterable $found): array
{
    $namesById = array_flip(array_map(fn (Stay $stay): string => $stay->id, $stays));

    return collect($found)->map(fn (Stay $stay): string => $namesById[$stay->id])->sort()->values()->all();
}

it('finds the stays of patients who are admitted today', function () {
    expect(stayNames($this->stays, Stay::query()->whereAdmitted()->get()))->toBe(['ending today', 'open', 'planned to end later']);
});

it('finds the stays that start after today', function () {
    expect(stayNames($this->stays, Stay::query()->whereScheduled()->get()))->toBe(['starting tomorrow']);
});

it('finds the stays that ended before today', function () {
    expect(stayNames($this->stays, Stay::query()->whereDischarged()->get()))->toBe(['over']);
});

it('combines the chosen statuses', function () {
    expect(stayNames($this->stays, Stay::query()->whereStatusIn([StayStatus::Scheduled, StayStatus::Discharged])->get()))
        ->toBe(['over', 'starting tomorrow']);
});

it('uses the hospital day to tell the statuses apart', function () {
    config(['app.hospital_timezone' => 'Asia/Colombo']);
    $this->travelTo('2026-10-15 20:00:00');

    expect(stayNames($this->stays, Stay::query()->whereScheduled()->get()))->toBe([]);
    expect(stayNames($this->stays, Stay::query()->whereDischarged()->get()))->toBe(['ending today', 'over']);
});
