function countOf(amount: number, unit: string): string {
    return `${amount} ${unit}${amount === 1 ? '' : 's'}`
}

export function formatAge(dateOfBirth: string, today: string): string {
    const [birthYear, birthMonth, birthDay] = dateOfBirth.split('-').map(Number)
    const [year, month, day] = today.split('-').map(Number)
    const fullMonths =
        (year - birthYear) * 12 +
        (month - birthMonth) -
        (day < birthDay ? 1 : 0)
    const years = Math.floor(fullMonths / 12)
    const months = fullMonths % 12

    if (years === 0) {
        return countOf(months, 'month')
    }

    return `${countOf(years, 'year')}, ${countOf(months, 'month')}`
}
