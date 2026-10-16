<?php

namespace App\Clinical\UseCases;

use App\Clinical\Entities\Models\Stay;
use App\Clinical\IO\Http\DTOs\AdmissionData;
use Illuminate\Support\Facades\DB;

final class AdmitPatient
{
    public function __construct(private EnsureAdmissionIsAllowed $ensureAdmissionIsAllowed) {}

    public function execute(AdmissionData $data): Stay
    {
        return DB::transaction(function () use ($data): Stay {
            $this->ensureAdmissionIsAllowed->execute($data);

            $stay = new Stay([
                'patient_id' => $data->patient->id,
                'room_id' => $data->room->id,
                'startDate' => $data->startDate->toDateString(),
                'endDate' => $data->endDate?->toDateString(),
            ]);
            $stay->save();

            return $stay->refresh()->load(ListStays::RELATIONS);
        });
    }
}
