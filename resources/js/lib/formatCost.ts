const costFormatter = new Intl.NumberFormat('en-US', {
    style: 'currency',
    currency: 'USD',
})

export function formatCost(cost: number): string {
    return costFormatter.format(cost)
}
