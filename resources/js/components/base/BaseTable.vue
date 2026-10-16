<script lang="ts">
import { ArrowDown, ArrowUp, ChevronsUpDown } from '@lucide/vue'
import type { PropType } from 'vue'
import { defineComponent } from 'vue'
import type { ColumnAlignment, SortDirection, TableSorting } from '@/types'

export default defineComponent({
    name: 'BaseTable',

    components: {
        ArrowDown,
        ArrowUp,
        ChevronsUpDown,
    },

    props: {
        columnHeadings: {
            type: Array as PropType<string[]>,
            required: true,
        },
        isLoading: {
            type: Boolean,
            default: false,
        },
        isEmpty: {
            type: Boolean,
            default: false,
        },
        emptyMessage: {
            type: String,
            default: 'Nothing to show yet.',
        },
        sortableColumns: {
            type: Object as PropType<Record<string, string>>,
            default: () => ({}),
        },
        sorting: {
            type: Object as PropType<TableSorting | null>,
            default: null,
        },
        firstSortDirections: {
            type: Object as PropType<Record<string, SortDirection>>,
            default: () => ({}),
        },
        columnAlignments: {
            type: Object as PropType<Record<string, ColumnAlignment>>,
            default: () => ({}),
        },
    },

    emits: ['sort'],

    data() {
        return {
            numberOfLoadingRows: 5,
        }
    },

    methods: {
        alignmentClassOf(columnHeading: string): string {
            return {
                left: 'text-left',
                center: 'text-center',
                right: 'text-right',
            }[this.columnAlignments[columnHeading] ?? 'left']
        },

        isSortable(columnHeading: string): boolean {
            return columnHeading in this.sortableColumns
        },

        isSortedBy(columnHeading: string): boolean {
            return this.sorting?.column === this.sortableColumns[columnHeading]
        },

        ariaSortOf(
            columnHeading: string,
        ): 'ascending' | 'descending' | undefined {
            if (!this.isSortedBy(columnHeading)) {
                return undefined
            }

            return this.sorting?.direction === 'desc'
                ? 'descending'
                : 'ascending'
        },

        sortBy(columnHeading: string): void {
            const column = this.sortableColumns[columnHeading]
            const firstDirection = this.firstSortDirections[column] ?? 'asc'
            const direction: SortDirection = this.isSortedBy(columnHeading)
                ? this.sorting?.direction === 'asc'
                    ? 'desc'
                    : 'asc'
                : firstDirection

            this.$emit('sort', { column, direction } as TableSorting)
        },
    },
})
</script>

<template>
    <div
        class="overflow-x-auto rounded-xl border border-neutral-200 dark:border-neutral-800"
    >
        <table class="w-full text-left text-sm">
            <thead
                class="border-b border-neutral-200 bg-neutral-50 dark:border-neutral-800 dark:bg-neutral-900"
            >
                <tr>
                    <th
                        v-for="columnHeading in columnHeadings"
                        :key="columnHeading"
                        class="px-4 py-3 text-xs font-medium tracking-wide text-neutral-500 uppercase dark:text-neutral-400"
                        :class="alignmentClassOf(columnHeading)"
                        :aria-sort="ariaSortOf(columnHeading)"
                    >
                        <button
                            v-if="isSortable(columnHeading)"
                            type="button"
                            class="group inline-flex cursor-pointer items-center gap-1 uppercase hover:text-neutral-900 dark:hover:text-neutral-100"
                            :class="{
                                'text-neutral-900 dark:text-neutral-100':
                                    isSortedBy(columnHeading),
                            }"
                            @click="sortBy(columnHeading)"
                        >
                            {{ columnHeading }}
                            <template v-if="isSortedBy(columnHeading)">
                                <ArrowDown
                                    v-if="sorting?.direction === 'desc'"
                                    class="size-3.5"
                                />
                                <ArrowUp v-else class="size-3.5" />
                            </template>
                            <ChevronsUpDown
                                v-else
                                class="size-3.5 opacity-40 group-hover:opacity-100"
                            />
                        </button>
                        <template v-else>{{ columnHeading }}</template>
                    </th>
                </tr>
            </thead>

            <tbody
                class="divide-y divide-neutral-200 bg-white dark:divide-neutral-800 dark:bg-neutral-950"
            >
                <template v-if="isLoading">
                    <tr
                        v-for="loadingRowNumber in numberOfLoadingRows"
                        :key="loadingRowNumber"
                    >
                        <td
                            v-for="columnHeading in columnHeadings"
                            :key="columnHeading"
                            class="px-4 py-3"
                        >
                            <div
                                class="h-4 w-3/4 animate-pulse rounded bg-neutral-200 dark:bg-neutral-800"
                            />
                        </td>
                    </tr>
                </template>

                <tr v-else-if="isEmpty">
                    <td
                        :colspan="columnHeadings.length"
                        class="px-4 py-10 text-center text-neutral-500 dark:text-neutral-400"
                    >
                        {{ emptyMessage }}
                    </td>
                </tr>

                <slot v-else />
            </tbody>
        </table>
    </div>
</template>
