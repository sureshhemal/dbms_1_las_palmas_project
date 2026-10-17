import type { AppointmentStatus } from '@/types'

export const appointmentStatusLabels: Record<AppointmentStatus, string> = {
    upcoming: 'Upcoming',
    in_progress: 'In progress',
    completed: 'Completed',
}

export const appointmentStatusClasses: Record<AppointmentStatus, string> = {
    upcoming: 'bg-info-100 text-info-800 dark:bg-info-950 dark:text-info-300',
    in_progress:
        'bg-warning-100 text-warning-800 dark:bg-warning-950 dark:text-warning-300',
    completed:
        'bg-neutral-100 text-neutral-700 dark:bg-neutral-800 dark:text-neutral-300',
}

export function appointmentStatusOf(
    appointment: { startDateTime: string; endDateTime: string },
    now: string,
): AppointmentStatus {
    if (new Date(appointment.startDateTime) > new Date(now)) {
        return 'upcoming'
    }

    return new Date(appointment.endDateTime) > new Date(now)
        ? 'in_progress'
        : 'completed'
}
