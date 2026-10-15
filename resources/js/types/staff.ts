export type PhysicianPosition =
    | 'Intern'
    | 'Surgeon'
    | 'Senior'
    | 'Chief of Medicine'
    | 'Resident'
    | 'Psychiatrist'

export const physicianPositions: PhysicianPosition[] = [
    'Intern',
    'Surgeon',
    'Senior',
    'Chief of Medicine',
    'Resident',
    'Psychiatrist',
]

export type PhysicianFormData = {
    id: string | null
    physicianID: string
    name: string
    position: PhysicianPosition | ''
    ssn: string
}

export type Physician = {
    id: string
    physicianID: number
    name: string
    position: PhysicianPosition
    ssn: string | null
    created_at: string | null
    updated_at: string | null
    deleted_at: string | null
}

export type NursePosition = 'Head Nurse' | 'Nurse'

export const nursePositions: NursePosition[] = ['Head Nurse', 'Nurse']

export type NurseFormData = {
    id: string | null
    nurseID: string
    name: string
    position: NursePosition | ''
    ssn: string
}

export type Nurse = {
    id: string
    nurseID: number
    name: string
    position: NursePosition
    ssn: string | null
    created_at: string | null
    updated_at: string | null
    deleted_at: string | null
}

export type StaffCounts = {
    physician_count: number
    nurse_count: number
}
