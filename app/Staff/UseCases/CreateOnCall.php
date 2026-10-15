<?php

namespace App\Staff\UseCases;

use App\Staff\Entities\Models\OnCall;
use App\Staff\IO\Http\DTOs\OnCallData;
use Illuminate\Support\Facades\DB;

final class CreateOnCall
{
    public function __construct(private EnsureOnCallDoesNotOverlap $ensureOnCallDoesNotOverlap) {}

    public function execute(OnCallData $data): OnCall
    {
        return DB::transaction(function () use ($data): OnCall {
            $this->ensureOnCallDoesNotOverlap->execute($data);

            $onCall = new OnCall([
                'nurse_id' => $data->nurseId,
                'startDate' => $data->startDate?->toDateString(),
                'endDate' => $data->endDate?->toDateString(),
            ]);
            $onCall->save();

            return $onCall->load('nurse:id,nurseID,name,position');
        });
    }
}
