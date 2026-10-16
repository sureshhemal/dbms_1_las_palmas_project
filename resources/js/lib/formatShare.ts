export function formatShare(share: number, count: number): string {
    if (count > 0 && share < 0.01) {
        return '< 0.01%'
    }

    return `${share.toFixed(2)}%`
}
