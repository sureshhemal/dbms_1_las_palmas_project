import type { Physician, PhysicianPosition } from './staff'

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

export type DepartmentPhysician = {
    id: string
    physicianID: number
    name: string
    position: PhysicianPosition
    joined_at: string | null
}

export type RoomType = 'Single' | 'Double'

export const roomTypes: RoomType[] = ['Single', 'Double']

export type RoomOccupant = {
    id: string
    patient: { id: string; name: string; deleted_at: string | null }
}

export type Room = {
    id: string
    roomID: number
    roomType: RoomType
    bedsFree: number
    current_stays?: RoomOccupant[]
    created_at: string | null
    updated_at: string | null
    deleted_at: string | null
}

export type RoomFormData = {
    id: string | null
    roomID: string
    roomType: RoomType | ''
}
