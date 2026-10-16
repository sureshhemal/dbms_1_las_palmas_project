<?php

namespace App\Clinical\UseCases;

use App\Clinical\Entities\BedOccupancy;
use App\Clinical\Entities\Models\Stay;
use App\Clinical\IO\Http\DTOs\ExtensionData;
use App\Hospital\Entities\HospitalCalendar;
use App\Hospital\Entities\Models\Room;
use App\Patient\Entities\Models\Patient;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

final class ExtendStay
{
    public function execute(ExtensionData $data): Stay
    {
        return DB::transaction(function () use ($data): Stay {
            $stay = $data->stay;
            $patient = Patient::query()->withTrashed()->lockForUpdate()->findOrFail($stay->patient_id);
            $room = Room::query()->withTrashed()->lockForUpdate()->findOrFail($stay->room_id);

            $problem = $this->problemWithEndDate($stay, $data->endDate)
                ?? $this->problemWithPatient($stay, $patient, $data->endDate)
                ?? $this->problemWithRoom($stay, $room, $data->endDate);

            if ($problem !== null) {
                throw ValidationException::withMessages(['endDate' => $problem]);
            }

            $stay->update(['endDate' => $data->endDate->toDateString()]);

            return $stay->load(ListStays::RELATIONS);
        });
    }

    private function problemWithEndDate(Stay $stay, CarbonImmutable $newEndDate): ?string
    {
        return match (true) {
            $stay->endDate === null => 'This stay has no end date yet, so there is nothing to extend.',
            $stay->endDate->lessThan(HospitalCalendar::today()) => "This stay already ended on {$stay->endDate->toDateString()}.",
            $newEndDate->lessThanOrEqualTo($stay->endDate) => "The new end date must be after {$stay->endDate->toDateString()}.",
            default => null,
        };
    }

    private function problemWithPatient(Stay $stay, Patient $patient, CarbonImmutable $newEndDate): ?string
    {
        $laterStay = Stay::query()
            ->where('patient_id', $patient->id)
            ->whereKeyNot($stay->id)
            ->whereOverlapping($stay->endDate ?? $stay->startDate, $newEndDate)
            ->oldest('startDate')
            ->first();

        return $laterStay instanceof Stay
            ? "{$patient->name} has another stay from {$laterStay->startDate->toDateString()}."
            : null;
    }

    private function problemWithRoom(Stay $stay, Room $room, CarbonImmutable $newEndDate): ?string
    {
        $firstExtraDay = $stay->endDate ?? $stay->startDate;
        $beds = $room->roomType->beds();
        $otherPatientsStays = Stay::query()
            ->where('room_id', $room->id)
            ->whereKeyNot($stay->id)
            ->whereOverlapping($firstExtraDay, $newEndDate)
            ->toBase()
            ->get(['startDate', 'endDate']);
        $occupancy = new BedOccupancy($otherPatientsStays);
        $fullDay = $occupancy->firstFullDayBetween($firstExtraDay->toDateString(), $newEndDate->toDateString(), $beds);

        return $fullDay === null
            ? null
            : "Room {$room->roomID} is full on {$fullDay} ({$occupancy->bedsTakenOn($fullDay)} of {$beds} beds taken).";
    }
}
