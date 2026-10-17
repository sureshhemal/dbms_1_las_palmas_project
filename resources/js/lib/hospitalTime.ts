import { calendarDateIn } from '@/lib/calendarDate'

export function hospitalDateOf(isoDateTime: string, timeZone: string): string {
    return calendarDateIn(timeZone, new Date(isoDateTime))
}

export function hospitalTimeOf(isoDateTime: string, timeZone: string): string {
    return new Intl.DateTimeFormat('en-GB', {
        timeZone,
        hour: '2-digit',
        minute: '2-digit',
        hourCycle: 'h23',
    }).format(new Date(isoDateTime))
}

export function minutesBetween(startTime: string, endTime: string): number {
    return minutesOf(endTime) - minutesOf(startTime)
}

export function addMinutesToTime(time: string, minutes: number): string {
    const total = Math.min(minutesOf(time) + minutes, 23 * 60 + 59)
    const hours = String(Math.floor(total / 60)).padStart(2, '0')

    return `${hours}:${String(total % 60).padStart(2, '0')}`
}

function minutesOf(time: string): number {
    const [hours, minutes] = time.split(':').map(Number)

    return hours * 60 + minutes
}
