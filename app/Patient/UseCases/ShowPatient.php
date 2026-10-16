<?php

namespace App\Patient\UseCases;

use App\Patient\Entities\Models\Patient;

final class ShowPatient
{
    public function execute(Patient $patient): Patient
    {
        return $patient->load([
            'primaryPhysician:id,physicianID,name,deleted_at',
            'currentStay:id,stayID,patient_id,room_id,startDate,endDate',
            'currentStay.room:id,roomID,roomType',
        ]);
    }
}
