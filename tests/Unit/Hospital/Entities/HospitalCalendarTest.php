<?php

use App\Hospital\Entities\HospitalCalendar;

it('uses the hospital timezone to decide which day it is', function (string $hospitalTimezone, string $now, string $expectedToday) {
    config(['app.hospital_timezone' => $hospitalTimezone]);
    $this->travelTo($now);

    expect(HospitalCalendar::today()->toDateString())->toBe($expectedToday);
})->with([
    'ahead of UTC' => ['Asia/Colombo', '2026-10-15 22:00:00', '2026-10-16'],
    'behind UTC' => ['America/Denver', '2026-10-15 03:00:00', '2026-10-14'],
    'UTC' => ['UTC', '2026-10-15 23:59:59', '2026-10-15'],
]);

it('returns the start of the day', function () {
    $this->travelTo('2026-10-15 13:45:00');

    expect(HospitalCalendar::today()->format('H:i:s'))->toBe('00:00:00');
});
