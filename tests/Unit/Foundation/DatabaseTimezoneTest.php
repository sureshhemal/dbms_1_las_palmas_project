<?php

use App\Patient\Entities\Models\Patient;
use Illuminate\Support\Facades\DB;

it('talks to MySQL in UTC like the application', function () {
    expect(DB::selectOne('select @@session.time_zone as time_zone')->time_zone)->toBe('+00:00');
});

it('saves a timestamp that falls in a daylight saving gap of the server time zone', function () {
    $this->travelTo('2024-03-10 02:21:30');

    $patient = Patient::factory()->create();

    expect($patient->fresh()->created_at->toDateTimeString())->toBe('2024-03-10 02:21:30');
});
