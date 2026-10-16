export type Medication = {
    id: string
    medID: number
    name: string
    created_at: string | null
    updated_at: string | null
    deleted_at: string | null
}

export type MedicationFormData = {
    id: string | null
    medID: string
    name: string
}

export type Procedure = {
    id: string
    procID: number
    name: string
    cost: number | null
    created_at: string | null
    updated_at: string | null
    deleted_at: string | null
}

export type ProcedureFormData = {
    id: string | null
    procID: string
    name: string
    cost: string
}

export type PrescriptionParty = {
    id: string
    name: string
    deleted_at: string | null
}

export type Prescription = {
    id: string
    prescribedDate: string
    dose: string
    patient: PrescriptionParty & { patientID: number }
    medication: PrescriptionParty & { medID: number }
    physician: PrescriptionParty & { physicianID: number }
    created_at: string | null
}

export type PrescriptionFormData = {
    patient_id: string
    medication_id: string
    physician_id: string
    prescribedDate: string
    dose: string
}

export type Prescriber = {
    id: string
    physicianID: number
    name: string
}

export type Stay = {
    id: string
    stayID: number
    startDate: string
    endDate: string | null
    patient: PrescriptionParty & { patientID: number }
    room: {
        id: string
        roomID: number
        roomType: string
        deleted_at: string | null
    }
}

export type RoomAvailability = {
    id: string
    roomID: number
    roomType: string
    beds: number
    bedsTaken: number
    bedsFree: number
}

export type PerformedProcedure = {
    id: string
    procDate: string
    patient: { id: string; patientID: number; name: string }
    procedure: { id: string; procID: number; name: string }
    stay: {
        id: string
        stayID: number
        startDate: string
        endDate: string | null
        room: { id: string; roomID: number }
    }
    physician: { id: string; physicianID: number; name: string }
    nurse: { id: string; nurseID: number; name: string }
}

export type ProcedureDetailsFormData = {
    procedure_id: string
    physician_id: string
    nurse_id: string
    procDate: string
}

export type ProcedureEntryFormData = ProcedureDetailsFormData & {
    patient_id: string
    room_id: string
    startDate: string
    endDate: string
}
