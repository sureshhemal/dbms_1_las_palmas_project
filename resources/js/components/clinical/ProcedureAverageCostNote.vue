<script lang="ts">
import { Calculator } from '@lucide/vue'
import type { PropType } from 'vue'
import { defineComponent } from 'vue'
import { formatCost } from '@/lib/formatCost'
import { formatCount } from '@/lib/formatCount'
import type { ProcedureAverageCost } from '@/types'

export default defineComponent({
    name: 'ProcedureAverageCostNote',

    components: {
        Calculator,
    },

    props: {
        averageCost: {
            type: Object as PropType<ProcedureAverageCost | null>,
            default: null,
        },
        isLoading: {
            type: Boolean,
            default: false,
        },
    },

    computed: {
        formattedAverageCost(): string {
            return this.averageCost?.averageCost === null ||
                this.averageCost?.averageCost === undefined
                ? 'Not available'
                : formatCost(this.averageCost.averageCost)
        },

        pricedProceduresLabel(): string {
            const count = this.averageCost?.pricedProcedureCount ?? 0

            return `across ${formatCount(count)} ${count === 1 ? 'procedure' : 'procedures'} with a cost`
        },

        unpricedProceduresLabel(): string {
            const count = this.averageCost?.unpricedProcedureCount ?? 0

            if (count === 0) {
                return ''
            }

            return `${formatCount(count)} without a cost ${count === 1 ? 'is' : 'are'} not counted`
        },
    },
})
</script>

<template>
    <div
        class="flex flex-wrap items-center gap-x-2 gap-y-1 text-sm text-neutral-600 dark:text-neutral-300"
    >
        <Calculator class="size-4 text-neutral-400 dark:text-neutral-500" />
        <span>Average cost</span>
        <span
            v-if="isLoading || !averageCost"
            class="h-4 w-20 animate-pulse rounded bg-neutral-200 dark:bg-neutral-800"
        />
        <template v-else>
            <span
                class="font-semibold text-neutral-900 tabular-nums dark:text-neutral-100"
            >
                {{ formattedAverageCost }}
            </span>
            <span class="text-neutral-500 dark:text-neutral-400">
                {{ pricedProceduresLabel }}
                <template v-if="unpricedProceduresLabel">
                    · {{ unpricedProceduresLabel }}
                </template>
            </span>
        </template>
    </div>
</template>
