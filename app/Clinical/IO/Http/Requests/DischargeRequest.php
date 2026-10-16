<?php

namespace App\Clinical\IO\Http\Requests;

use App\Clinical\Entities\Models\Stay;
use App\Clinical\IO\Http\DTOs\DischargeData;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Http\FormRequest;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class DischargeRequest extends FormRequest
{
    public function getData(): DischargeData
    {
        return new DischargeData(
            stay: $this->stay(),
            endDate: CarbonImmutable::createFromFormat('!Y-m-d', $this->string('endDate')->toString()),
        );
    }

    private function stay(): Stay
    {
        $stay = $this->route('stay');
        $stay = $stay instanceof Stay ? $stay : Stay::query()->find($stay);

        if (! $stay instanceof Stay) {
            throw new NotFoundHttpException('Stay not found.');
        }

        return $stay;
    }
}
