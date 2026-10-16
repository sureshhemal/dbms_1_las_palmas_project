<script lang="ts">
import { Pencil, Trash2 } from '@lucide/vue'
import type { PropType } from 'vue'
import { defineComponent } from 'vue'
import BaseTableActionButton from '@/components/base/BaseTableActionButton.vue'
import BaseTableCell from '@/components/base/BaseTableCell.vue'
import BaseTableRow from '@/components/base/BaseTableRow.vue'
import { formatCost } from '@/lib/formatCost'
import { formatCount } from '@/lib/formatCount'
import { formatShare } from '@/lib/formatShare'
import type { Procedure } from '@/types'

export default defineComponent({
    name: 'ProceduresTableRow',

    components: {
        BaseTableActionButton,
        BaseTableCell,
        BaseTableRow,
        Pencil,
        Trash2,
    },

    props: {
        procedure: {
            type: Object as PropType<Procedure>,
            required: true,
        },
    },

    emits: ['edit', 'remove'],

    computed: {
        hasCost(): boolean {
            return this.procedure.cost !== null
        },

        formattedCost(): string {
            return this.procedure.cost !== null
                ? formatCost(this.procedure.cost)
                : 'Not set'
        },

        performedCount(): number {
            return this.procedure.performedCount ?? 0
        },

        performedShare(): string {
            return formatShare(
                this.procedure.performedShare ?? 0,
                this.performedCount,
            )
        },

        performedCountLabel(): string {
            return `Performed ${formatCount(this.performedCount)} ${this.performedCount === 1 ? 'time' : 'times'}`
        },
    },
})
</script>

<template>
    <BaseTableRow>
        <BaseTableCell align="center" class="font-mono">
            {{ procedure.procID }}
        </BaseTableCell>
        <BaseTableCell class="font-medium">
            {{ procedure.name }}
        </BaseTableCell>
        <BaseTableCell
            align="center"
            class="tabular-nums"
            :class="{
                'text-neutral-500 dark:text-neutral-400': performedCount === 0,
            }"
            :title="performedCountLabel"
        >
            {{ performedShare }}
        </BaseTableCell>
        <BaseTableCell
            align="right"
            class="tabular-nums"
            :class="{ 'text-neutral-500 dark:text-neutral-400': !hasCost }"
        >
            {{ formattedCost }}
        </BaseTableCell>
        <BaseTableCell align="right" class="w-0">
            <div class="flex items-center justify-end gap-1">
                <BaseTableActionButton
                    tone="brand"
                    @click.stop="$emit('edit', procedure)"
                >
                    <Pencil />
                    Edit
                </BaseTableActionButton>
                <BaseTableActionButton
                    tone="danger"
                    @click.stop="$emit('remove', procedure)"
                >
                    <Trash2 />
                    Remove
                </BaseTableActionButton>
            </div>
        </BaseTableCell>
    </BaseTableRow>
</template>
