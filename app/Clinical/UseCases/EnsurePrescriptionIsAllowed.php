<?php

namespace App\Clinical\UseCases;

use App\Clinical\Entities\Models\Medication;
use App\Clinical\IO\Http\DTOs\PrescriptionData;
use App\Hospital\Entities\HospitalCalendar;
use App\Patient\Entities\Models\Patient;
use App\Staff\Entities\Models\Physician;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Validation\ValidationException;

final class EnsurePrescriptionIsAllowed
{
    public function execute(PrescriptionData $data): void
    {
        $problems = array_filter([
            'prescribedDate' => $this->problemWithDate($data),
            'physician_id' => $this->problemWithPhysician($data),
            'patient_id' => $this->problemWithPatient($data),
            'medication_id' => $this->problemWithMedication($data),
        ]);

        if ($problems !== []) {
            throw ValidationException::withMessages($problems);
        }
    }

    private function problemWithDate(PrescriptionData $data): ?string
    {
        if ($data->prescribedDate === null) {
            return null;
        }

        if ($data->prescribedDate->greaterThan(HospitalCalendar::today())) {
            return 'A prescription cannot be dated in the future.';
        }

        $patient = $data->patientId === null ? null : Patient::query()->find($data->patientId);

        if (! $patient instanceof Patient || $patient->created_at === null) {
            return null;
        }

        $registeredOn = $patient->created_at->setTimezone(HospitalCalendar::timezone())->toDateString();

        return $data->prescribedDate->toDateString() < $registeredOn
            ? "{$patient->name} registered on {$registeredOn}, so a prescription cannot be dated earlier."
            : null;
    }

    private function problemWithPhysician(PrescriptionData $data): ?string
    {
        $physician = $data->physicianId === null
            ? null
            : Physician::query()->withoutGlobalScope(SoftDeletingScope::class)->find($data->physicianId);

        if (! $physician instanceof Physician) {
            return null;
        }

        if ($physician->trashed()) {
            return "{$physician->name} has been offboarded and cannot prescribe.";
        }

        return $physician->position->isCapableOfPrescribing()
            ? null
            : "{$physician->name} is an intern and cannot prescribe.";
    }

    private function problemWithPatient(PrescriptionData $data): ?string
    {
        $patient = $data->patientId === null ? null : Patient::onlyTrashed()->find($data->patientId);

        return $patient instanceof Patient
            ? "{$patient->name} has been removed and cannot be prescribed to."
            : null;
    }

    private function problemWithMedication(PrescriptionData $data): ?string
    {
        $medication = $data->medicationId === null ? null : Medication::onlyTrashed()->find($data->medicationId);

        return $medication instanceof Medication
            ? "{$medication->name} has been removed and cannot be prescribed."
            : null;
    }
}
