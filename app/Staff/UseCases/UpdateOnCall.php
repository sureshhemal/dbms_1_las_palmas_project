<?php

namespace App\Staff\UseCases;

use App\Staff\Entities\Models\OnCall;
use App\Staff\IO\Http\DTOs\OnCallData;
use Illuminate\Support\Facades\DB;

final class UpdateOnCall
{
    public function __construct(private EnsureOnCallDoesNotOverlap $ensureOnCallDoesNotOverlap) {}

    public function execute(OnCall $onCall, OnCallData $data): OnCall
    {
        return DB::transaction(function () use ($onCall, $data): OnCall {
            $this->ensureOnCallDoesNotOverlap->execute($data, $onCall);

            $onCall->update([
                'nurse_id' => $data->nurseId,
                'startDate' => $data->startDate?->toDateString(),
                'endDate' => $data->endDate?->toDateString(),
            ]);

            return $onCall->load('nurse:id,nurseID,name,position');
        });
    }
}
