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
