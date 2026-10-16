<script lang="ts">
import type { PropType } from 'vue'
import { Pencil, UserMinus } from '@lucide/vue'
import { defineComponent } from 'vue'
import BaseTableActionButton from '@/components/base/BaseTableActionButton.vue'
import BaseTableCell from '@/components/base/BaseTableCell.vue'
import BaseTableRow from '@/components/base/BaseTableRow.vue'
import { maskSsn } from '@/lib/maskSsn'
import type { Nurse } from '@/types'

export default defineComponent({
    name: 'NursesTableRow',

    components: {
        BaseTableActionButton,
        Pencil,
        UserMinus,
        BaseTableCell,
        BaseTableRow,
    },

    props: {
        nurse: {
            type: Object as PropType<Nurse>,
            required: true,
        },
    },

    emits: ['edit', 'offboard'],

    computed: {
        maskedSsn(): string {
            return this.nurse.ssn ? maskSsn(this.nurse.ssn) : '—'
        },
    },
})
</script>

<template>
    <BaseTableRow>
        <BaseTableCell align="center" class="font-mono">
            {{ nurse.nurseID }}
        </BaseTableCell>
        <BaseTableCell class="font-medium">
            {{ nurse.name }}
        </BaseTableCell>
        <BaseTableCell>{{ nurse.position }}</BaseTableCell>
        <BaseTableCell
            align="center"
            class="font-mono text-neutral-500 dark:text-neutral-400"
        >
            {{ maskedSsn }}
        </BaseTableCell>
        <BaseTableCell align="right" class="w-0">
            <div class="flex items-center justify-end gap-1">
                <BaseTableActionButton
                    tone="brand"
                    @click.stop="$emit('edit', nurse)"
                >
                    <Pencil />
                    Edit
                </BaseTableActionButton>
                <BaseTableActionButton
                    tone="danger"
                    @click.stop="$emit('offboard', nurse)"
                >
                    <UserMinus />
                    Offboard
                </BaseTableActionButton>
            </div>
        </BaseTableCell>
    </BaseTableRow>
</template>
