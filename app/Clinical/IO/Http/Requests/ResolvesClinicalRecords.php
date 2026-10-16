<?php

namespace App\Clinical\IO\Http\Requests;

use App\Clinical\Entities\Models\Procedure;
use App\Clinical\Entities\Models\Stay;
use App\Clinical\IO\Http\DTOs\ProcedureDetails;
use App\Staff\Entities\Models\Nurse;
use App\Staff\Entities\Models\Physician;
use Carbon\CarbonImmutable;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

trait ResolvesClinicalRecords
{
    private function procedureDetails(): ProcedureDetails
    {
        return new ProcedureDetails(
            procedure: $this->procedure(),
            physician: $this->physician(),
            nurse: $this->nurse(),
            procDate: $this->calendarDate('procDate'),
        );
    }

    private function stayInRoute(): Stay
    {
        $stay = $this->route('stay');
        $stay = $stay instanceof Stay ? $stay : Stay::query()->find($stay);

        return $stay instanceof Stay ? $stay : throw new NotFoundHttpException('Stay not found.');
    }

    private function calendarDate(string $key): CarbonImmutable
    {
        return CarbonImmutable::createFromFormat('!Y-m-d', $this->string($key)->toString());
    }

    private function optionalCalendarDate(string $key): ?CarbonImmutable
    {
        return $this->filled($key) ? $this->calendarDate($key) : null;
    }

    private function procedure(): Procedure
    {
        $procedure = Procedure::query()->find($this->string('procedure_id')->toString());

        return $procedure instanceof Procedure ? $procedure : throw new NotFoundHttpException('Procedure not found.');
    }

    private function physician(): Physician
    {
        $physician = Physician::query()->find($this->string('physician_id')->toString());

        return $physician instanceof Physician ? $physician : throw new NotFoundHttpException('Physician not found.');
    }

    private function nurse(): Nurse
    {
        $nurse = Nurse::query()->find($this->string('nurse_id')->toString());

        return $nurse instanceof Nurse ? $nurse : throw new NotFoundHttpException('Nurse not found.');
    }
}
