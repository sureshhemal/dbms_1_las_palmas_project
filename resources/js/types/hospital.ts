import type { Physician } from './staff'

export type DepartmentName = 'General Medicine' | 'Surgery' | 'Psychiatry'

export type Department = {
    id: string
    deptID: number
    name: DepartmentName
    head_id: string | null
    head: Physician | null
    created_at: string | null
    updated_at: string | null
    deleted_at: string | null
}

export type DepartmentFormData = {
    id: string
    deptID: string
    name: DepartmentName
    head_id: string | null
}
