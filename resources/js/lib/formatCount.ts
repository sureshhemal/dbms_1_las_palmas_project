const countFormatter = new Intl.NumberFormat('en-US')

export function formatCount(count: number): string {
    return countFormatter.format(count)
}
