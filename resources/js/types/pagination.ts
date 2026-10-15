export type PaginationLink = {
    url: string | null
    label: string
    page?: number | null
    active: boolean
}

export type PaginationDetails = {
    current_page: number
    per_page: number
    from: number | null
    to: number | null
    total: number
    last_page: number
    links: PaginationLink[]
}

export type PaginatedResponse<Item> = PaginationDetails & {
    data: Item[]
}
