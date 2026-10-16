<?php

namespace App\Clinical\UseCases;

use App\Clinical\Entities\BedOccupancy;
use App\Clinical\Entities\Models\Stay;
use App\Clinical\IO\Http\DTOs\AdmissionData;
use App\Hospital\Entities\HospitalCalendar;
use App\Hospital\Entities\Models\Room;
use App\Patient\Entities\Models\Patient;
use Carbon\CarbonImmutable;
use Illuminate\Validation\ValidationException;

final class EnsureAdmissionIsAllowed
{
    public function execute(AdmissionData $data): void
    {
        $patient = Patient::query()->lockForUpdate()->findOrFail($data->patient->id);
        $room = Room::query()->lockForUpdate()->findOrFail($data->room->id);

        $problems = array_filter([
            'startDate' => $this->problemWithDate($data->startDate, $patient),
            'endDate' => $this->problemWithDischargeDate($data->endDate),
            'patient_id' => $this->problemWithPatient($data, $patient),
            'room_id' => $this->problemWithRoom($data, $room),
        ]);

        if (! blank($problems)) {
            throw ValidationException::withMessages($problems);
        }
    }

    private function problemWithDate(CarbonImmutable $startDate, Patient $patient): ?string
    {
        if ($startDate->greaterThan(HospitalCalendar::today())) {
            return 'An admission cannot be dated in the future.';
        }

        $registeredOn = $patient->created_at->setTimezone(HospitalCalendar::timezone())->toDateString();

        return $startDate->toDateString() < $registeredOn
            ? "{$patient->name} registered on {$registeredOn}, so an admission cannot be dated earlier."
            : null;
    }

    private function problemWithDischargeDate(?CarbonImmutable $endDate): ?string
    {
        return $endDate?->greaterThan(HospitalCalendar::today())
            ? 'A discharge cannot be dated in the future.'
            : null;
    }

    private function problemWithPatient(AdmissionData $data, Patient $patient): ?string
    {
        $ongoingStay = Stay::query()
            ->where('patient_id', $patient->id)
            ->whereOverlapping($data->startDate, $data->endDate)
            ->oldest('startDate')
            ->first();

        return $ongoingStay instanceof Stay
            ? "{$patient->name} is already admitted since {$ongoingStay->startDate->toDateString()}."
            : null;
    }

    private function problemWithRoom(AdmissionData $data, Room $room): ?string
    {
        $beds = $room->roomType->beds();
        $overlappingStays = Stay::query()
            ->where('room_id', $room->id)
            ->whereOverlapping($data->startDate, $data->endDate)
            ->toBase()
            ->get(['startDate', 'endDate']);
        $occupancy = new BedOccupancy($overlappingStays);
        $fullDay = $occupancy->firstFullDayBetween($data->startDate->toDateString(), $data->endDate?->toDateString(), $beds);

        return $fullDay === null
            ? null
            : "Room {$room->roomID} is full on {$fullDay} ({$occupancy->bedsTakenOn($fullDay)} of {$beds} beds taken).";
    }
}
