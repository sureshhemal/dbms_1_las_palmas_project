<script lang="ts">
import { ChevronLeft, ChevronRight } from '@lucide/vue'
import type { PropType } from 'vue'
import { defineComponent } from 'vue'
import { Label } from '@/components/ui/label'
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select'
import { formatCount } from '@/lib/formatCount'
import type { PaginationDetails, PaginationLink } from '@/types'

export default defineComponent({
    name: 'BasePaginationBar',

    components: {
        ChevronLeft,
        ChevronRight,
        SelectContent,
        SelectItem,
        SelectTrigger,
        SelectValue,
        UiLabel: Label,
        UiSelect: Select,
    },

    props: {
        paginationDetails: {
            type: Object as PropType<PaginationDetails>,
            required: true,
        },
    },

    emits: ['change-page', 'change-per-page'],

    data() {
        return {
            perPageOptions: [10, 20, 50, 100],
        }
    },

    computed: {
        perPageFieldId(): string {
            return `per-page-${this.$.uid}`
        },

        resultRangeSummary(): string {
            const { from, to, total } = this.paginationDetails

            return `Showing ${formatCount(from ?? 0)} – ${formatCount(to ?? 0)} of ${formatCount(total)} results`
        },

        hasMoreThanOnePage(): boolean {
            return this.paginationDetails.last_page > 1
        },

        isBusy(): boolean {
            return this.$wait.any() > 0
        },
    },

    methods: {
        pageLabel(paginationLink: PaginationLink): string {
            return /^\d+$/.test(paginationLink.label)
                ? formatCount(Number(paginationLink.label))
                : paginationLink.label
        },

        changePerPage(selectedPerPage: unknown): void {
            const perPage = Number(selectedPerPage)

            if (perPage === this.paginationDetails.per_page) {
                return
            }

            this.$emit('change-per-page', perPage)
        },

        changePage(paginationLink: PaginationLink): void {
            if (paginationLink.active || !paginationLink.page) {
                return
            }

            this.$emit('change-page', paginationLink.page)
        },

        isPreviousPageLink(linkPosition: number): boolean {
            return linkPosition === 0
        },

        isNextPageLink(linkPosition: number): boolean {
            return linkPosition === this.paginationDetails.links.length - 1
        },

        linkAccessibleName(linkPosition: number): string | undefined {
            if (this.isPreviousPageLink(linkPosition)) {
                return 'Previous page'
            }

            if (this.isNextPageLink(linkPosition)) {
                return 'Next page'
            }

            return undefined
        },
    },
})
</script>

<template>
    <div
        class="flex items-center justify-between gap-4 rounded-xl border border-neutral-200 bg-white px-4 py-2 dark:border-neutral-800 dark:bg-neutral-950"
    >
        <div class="flex items-center gap-2">
            <UiLabel :for="perPageFieldId" class="whitespace-nowrap"
                >Per page</UiLabel
            >
            <UiSelect
                :model-value="String(paginationDetails.per_page)"
                :disabled="isBusy"
                @update:model-value="changePerPage"
            >
                <SelectTrigger :id="perPageFieldId" class="h-8 w-20">
                    <SelectValue />
                </SelectTrigger>
                <SelectContent>
                    <SelectItem
                        v-for="perPageOption in perPageOptions"
                        :key="perPageOption"
                        :value="String(perPageOption)"
                    >
                        {{ perPageOption }}
                    </SelectItem>
                </SelectContent>
            </UiSelect>
        </div>

        <p class="text-xs text-neutral-500 dark:text-neutral-400">
            {{ resultRangeSummary }}
        </p>

        <nav
            v-if="hasMoreThanOnePage"
            class="flex items-center gap-1"
            aria-label="Pagination"
        >
            <button
                v-for="(
                    paginationLink, linkPosition
                ) in paginationDetails.links"
                :key="linkPosition"
                type="button"
                class="flex h-8 min-w-8 items-center justify-center rounded-md border px-2 text-sm transition-colors"
                :class="
                    paginationLink.active
                        ? 'border-brand-700 bg-brand-700 text-white dark:border-brand-400 dark:bg-brand-400 dark:text-brand-950'
                        : 'border-neutral-200 text-neutral-900 hover:bg-neutral-50 disabled:cursor-not-allowed disabled:opacity-50 dark:border-neutral-800 dark:text-neutral-100 dark:hover:bg-neutral-900'
                "
                :disabled="
                    paginationLink.active || !paginationLink.page || isBusy
                "
                :aria-label="linkAccessibleName(linkPosition)"
                @click="changePage(paginationLink)"
            >
                <ChevronLeft
                    v-if="isPreviousPageLink(linkPosition)"
                    class="size-4"
                />
                <ChevronRight
                    v-else-if="isNextPageLink(linkPosition)"
                    class="size-4"
                />
                <template v-else>
                    {{ pageLabel(paginationLink) }}
                </template>
            </button>
        </nav>
    </div>
</template>
