<?php

namespace App\Staff\Entities;

use App\Foundation\Entities\Concerns\HasValues;

enum PhysicianPosition: string
{
    use HasValues;

    case Intern = 'Intern';
    case Surgeon = 'Surgeon';
    case Senior = 'Senior';
    case ChiefOfMedicine = 'Chief of Medicine';
    case Resident = 'Resident';
    case Psychiatrist = 'Psychiatrist';

    public function isCapableOfBeingDepartmentHead(): bool
    {
        return match ($this) {
            self::Senior, self::Surgeon, self::Psychiatrist, self::ChiefOfMedicine => true,
            self::Intern, self::Resident => false,
        };
    }

    public function isCapableOfBeingPrimaryPhysician(): bool
    {
        return $this !== self::Intern;
    }

    public function isCapableOfPrescribing(): bool
    {
        return $this !== self::Intern;
    }

    public static function capableOfBeingDepartmentHead(): array
    {
        return array_values(array_filter(
            self::cases(),
            fn (self $position): bool => $position->isCapableOfBeingDepartmentHead(),
        ));
    }

    public static function capableOfPrescribing(): array
    {
        return array_values(array_filter(
            self::cases(),
            fn (self $position): bool => $position->isCapableOfPrescribing(),
        ));
    }

    public static function capableOfBeingPrimaryPhysician(): array
    {
        return array_values(array_filter(
            self::cases(),
            fn (self $position): bool => $position->isCapableOfBeingPrimaryPhysician(),
        ));
    }
}
