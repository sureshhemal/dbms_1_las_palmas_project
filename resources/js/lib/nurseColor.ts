const nurseColors = [
    '#0369a1',
    '#047857',
    '#b45309',
    '#be123c',
    '#6d28d9',
    '#0f766e',
    '#4338ca',
    '#a21caf',
    '#4d7c0f',
    '#c2410c',
]

export function nurseColor(nurseId: string): string {
    let hash = 7

    for (let index = 0; index < nurseId.length; index++) {
        hash = (hash * 31 + nurseId.charCodeAt(index)) % 1000003
    }

    return nurseColors[hash % nurseColors.length] ?? '#0369a1'
}
