<script lang="ts">
import { Pencil, Trash2 } from '@lucide/vue'
import type { PropType } from 'vue'
import { defineComponent } from 'vue'
import BaseTableActionButton from '@/components/base/BaseTableActionButton.vue'
import BaseTableCell from '@/components/base/BaseTableCell.vue'
import BaseTableRow from '@/components/base/BaseTableRow.vue'
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
})
</script>

<template>
    <BaseTableRow>
        <BaseTableCell class="font-mono">
            {{ medication.medID }}
        </BaseTableCell>
        <BaseTableCell class="font-medium">
            {{ medication.name }}
        </BaseTableCell>
        <BaseTableCell class="w-0 text-right">
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
