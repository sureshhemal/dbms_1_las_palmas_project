<script lang="ts">
import type { PropType } from 'vue'
import { Pencil, UserMinus } from '@lucide/vue'
import { defineComponent } from 'vue'
import BaseTableActionButton from '@/components/base/BaseTableActionButton.vue'
import BaseTableCell from '@/components/base/BaseTableCell.vue'
import BaseTableRow from '@/components/base/BaseTableRow.vue'
import { maskSsn } from '@/lib/maskSsn'
import type { Physician } from '@/types'

export default defineComponent({
    name: 'PhysiciansTableRow',

    components: {
        BaseTableActionButton,
        Pencil,
        UserMinus,
        BaseTableCell,
        BaseTableRow,
    },

    props: {
        physician: {
            type: Object as PropType<Physician>,
            required: true,
        },
    },

    emits: ['edit', 'offboard'],

    computed: {
        maskedSsn(): string {
            return this.physician.ssn ? maskSsn(this.physician.ssn) : '—'
        },
    },
})
</script>

<template>
    <BaseTableRow>
        <BaseTableCell class="font-mono">
            {{ physician.physicianID }}
        </BaseTableCell>
        <BaseTableCell class="font-medium">
            {{ physician.name }}
        </BaseTableCell>
        <BaseTableCell>{{ physician.position }}</BaseTableCell>
        <BaseTableCell class="font-mono text-neutral-500 dark:text-neutral-400">
            {{ maskedSsn }}
        </BaseTableCell>
        <BaseTableCell class="w-0 text-right">
            <div class="flex items-center justify-end gap-1">
                <BaseTableActionButton
                    tone="brand"
                    @click.stop="$emit('edit', physician)"
                >
                    <Pencil />
                    Edit
                </BaseTableActionButton>
                <BaseTableActionButton
                    tone="danger"
                    @click.stop="$emit('offboard', physician)"
                >
                    <UserMinus />
                    Offboard
                </BaseTableActionButton>
            </div>
        </BaseTableCell>
    </BaseTableRow>
</template>
