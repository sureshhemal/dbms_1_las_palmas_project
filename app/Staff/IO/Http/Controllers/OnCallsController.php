<?php

namespace App\Staff\IO\Http\Controllers;

use App\Staff\Entities\Models\OnCall;
use App\Staff\IO\Http\Requests\OnCallRequest;
use App\Staff\UseCases\CreateOnCall;
use App\Staff\UseCases\ListOnCalls;
use App\Staff\UseCases\RemoveOnCall;
use App\Staff\UseCases\UpdateOnCall;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Str;

class OnCallsController
{
    public function index(Request $request, ListOnCalls $listOnCalls): JsonResponse
    {
        return response()->json($listOnCalls->execute(
            from: $request->date('from', '!Y-m-d'),
            to: $request->date('to', '!Y-m-d'),
            nurseIds: collect($request->array('nurse_ids'))
                ->filter(fn (mixed $nurseId): bool => Str::isUlid($nurseId))
                ->values()
                ->all(),
        ));
    }

    public function store(OnCallRequest $request, CreateOnCall $createOnCall): JsonResponse
    {
        return response()->json($createOnCall->execute($request->getData()), 201);
    }

    public function update(OnCall $onCall, OnCallRequest $request, UpdateOnCall $updateOnCall): JsonResponse
    {
        return response()->json($updateOnCall->execute($onCall, $request->getData()));
    }

    public function destroy(OnCall $onCall, RemoveOnCall $removeOnCall): Response
    {
        $removeOnCall->execute($onCall);

        return response()->noContent();
    }
}
