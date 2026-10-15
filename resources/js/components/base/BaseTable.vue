<script lang="ts">
import type { PropType } from 'vue'
import { defineComponent } from 'vue'

export default defineComponent({
    name: 'BaseTable',

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
    },

    data() {
        return {
            numberOfLoadingRows: 5,
        }
    },
})
</script>

<template>
    <div
        class="overflow-hidden rounded-xl border border-neutral-200 dark:border-neutral-800"
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
                    >
                        {{ columnHeading }}
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
