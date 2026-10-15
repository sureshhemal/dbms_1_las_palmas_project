<?php

use App\Staff\Entities\Models\Physician;
use App\Staff\Entities\PhysicianPosition;

it('finds only physicians whose position lets them head a department', function () {
    foreach (PhysicianPosition::cases() as $position) {
        Physician::factory()->withPosition($position)->create();
    }

    $capablePhysicians = Physician::query()->whereCapableOfBeingDepartmentHeadBasedOnPosition()->get();

    expect($capablePhysicians->pluck('position')->all())->toEqualCanonicalizing([
        PhysicianPosition::Senior,
        PhysicianPosition::Surgeon,
        PhysicianPosition::Psychiatrist,
        PhysicianPosition::ChiefOfMedicine,
    ]);
});

it('leaves out interns and residents', function (PhysicianPosition $traineePosition) {
    Physician::factory()->withPosition($traineePosition)->create();

    expect(Physician::query()->whereCapableOfBeingDepartmentHeadBasedOnPosition()->exists())->toBeFalse();
})->with([
    'intern' => [PhysicianPosition::Intern],
    'resident' => [PhysicianPosition::Resident],
]);

it('finds every physician except interns as possible primary physicians', function () {
    foreach (PhysicianPosition::cases() as $position) {
        Physician::factory()->withPosition($position)->create();
    }

    $possiblePrimaryPhysicians = Physician::query()->whereCapableOfBeingPrimaryPhysicianBasedOnPosition()->get();

    expect($possiblePrimaryPhysicians->pluck('position')->all())->toEqualCanonicalizing([
        PhysicianPosition::Senior,
        PhysicianPosition::Surgeon,
        PhysicianPosition::Psychiatrist,
        PhysicianPosition::ChiefOfMedicine,
        PhysicianPosition::Resident,
    ]);
});
