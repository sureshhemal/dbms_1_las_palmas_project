<script lang="ts">
import { Pencil, Trash2 } from '@lucide/vue'
import type { PropType } from 'vue'
import { defineComponent } from 'vue'
import BaseTableActionButton from '@/components/base/BaseTableActionButton.vue'
import BaseTableCell from '@/components/base/BaseTableCell.vue'
import BaseTableRow from '@/components/base/BaseTableRow.vue'
import { formatCount } from '@/lib/formatCount'
import { formatShare } from '@/lib/formatShare'
import type { Medication } from '@/types'

export default defineComponent({
    name: 'MedicationsTableRow',

    components: {
        BaseTableActionButton,
        BaseTableCell,
        BaseTableRow,
        Pencil,
        Trash2,
    },

    props: {
        medication: {
            type: Object as PropType<Medication>,
            required: true,
        },
    },

    emits: ['edit', 'remove'],

    computed: {
        prescriptionsCount(): number {
            return this.medication.prescriptionsCount ?? 0
        },

        prescriptionShare(): string {
            return formatShare(
                this.medication.prescriptionShare ?? 0,
                this.prescriptionsCount,
            )
        },

        prescriptionsCountLabel(): string {
            return `${formatCount(this.prescriptionsCount)} ${this.prescriptionsCount === 1 ? 'prescription' : 'prescriptions'}`
        },
    },
})
</script>

<template>
    <BaseTableRow>
        <BaseTableCell align="center" class="font-mono">
            {{ medication.medID }}
        </BaseTableCell>
        <BaseTableCell class="font-medium">
            {{ medication.name }}
        </BaseTableCell>
        <BaseTableCell
            align="center"
            class="tabular-nums"
            :class="{
                'text-neutral-500 dark:text-neutral-400':
                    prescriptionsCount === 0,
            }"
            :title="prescriptionsCountLabel"
        >
            {{ prescriptionShare }}
        </BaseTableCell>
        <BaseTableCell align="right" class="w-0">
            <div class="flex items-center justify-end gap-1">
                <BaseTableActionButton
                    tone="brand"
                    @click.stop="$emit('edit', medication)"
                >
                    <Pencil />
                    Edit
                </BaseTableActionButton>
                <BaseTableActionButton
                    tone="danger"
                    @click.stop="$emit('remove', medication)"
                >
                    <Trash2 />
                    Remove
                </BaseTableActionButton>
            </div>
        </BaseTableCell>
    </BaseTableRow>
</template>
