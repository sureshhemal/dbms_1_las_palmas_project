export function todayIn(timeZone: string): string {
    return new Intl.DateTimeFormat('en-CA', {
        timeZone,
        year: 'numeric',
        month: '2-digit',
        day: '2-digit',
    }).format(new Date())
}

export function addDays(calendarDate: string, days: number): string {
    const date = new Date(`${calendarDate}T00:00:00Z`)

    date.setUTCDate(date.getUTCDate() + days)

    return date.toISOString().slice(0, 10)
}
