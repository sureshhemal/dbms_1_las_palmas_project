export type PrimaryPhysician = {
    id: string
    physicianID: number
    name: string
    deleted_at?: string | null
}

export type Patient = {
    id: string
    patientID: number
    name: string
    ssn: string | null
    address: string | null
    dob: string | null
    age: number | null
    phone: string | null
    insuranceNumber: number | null
    primary_physician_id: string | null
    primary_physician: PrimaryPhysician | null
    created_at: string | null
    updated_at: string | null
    deleted_at: string | null
}

export type PatientFormData = {
    id: string | null
    patientID: string
    name: string
    ssn: string
    address: string
    dob: string
    phone: string
    insuranceNumber: string
    primary_physician_id: string
}
