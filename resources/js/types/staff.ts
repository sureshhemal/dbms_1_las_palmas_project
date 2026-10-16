import type { DepartmentName } from './hospital'

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
    department_ids: string[]
}

export type DepartmentSummary = {
    id: string
    deptID: number
    name: DepartmentName
}

export type AffiliatedDepartment = {
    id: string
    name: DepartmentName
}

export type Physician = {
    id: string
    physicianID: number
    name: string
    position: PhysicianPosition
    ssn: string | null
    headed_departments?: DepartmentSummary[]
    departments?: AffiliatedDepartment[]
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

export type NurseSummary = {
    id: string
    nurseID: number
    name: string
    position: NursePosition
}

export type OnCall = {
    id: string
    nurse_id: string
    startDate: string
    endDate: string
    nurse: NurseSummary
    created_at: string | null
    updated_at: string | null
}

export type OnCallFormData = {
    id: string | null
    nurse_id: string
    startDate: string
    endDate: string
}

export type OnCallDates = {
    startDate: string
    endDate: string
}

export type OnCallDatesChange = OnCallDates & {
    onCall: OnCall
    revert: () => void
}

export type CalendarRange = {
    from: string
    to: string
}

export type StaffCounts = {
    physician_count: number
    nurse_count: number
    on_call_nurse_count: number
}

export type PhysicianProfile = Physician & {
    primary_patients_count: number
}
