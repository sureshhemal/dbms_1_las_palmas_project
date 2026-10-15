<script lang="ts">
import type { PropType } from 'vue'
import { defineComponent } from 'vue'
import BaseTableCell from '@/components/base/BaseTableCell.vue'
import BaseTableRow from '@/components/base/BaseTableRow.vue'
import { maskSsn } from '@/lib/maskSsn'
import type { Nurse } from '@/types'

export default defineComponent({
    name: 'NursesTableRow',

    components: {
        BaseTableCell,
        BaseTableRow,
    },

    props: {
        nurse: {
            type: Object as PropType<Nurse>,
            required: true,
        },
    },

    emits: ['select'],

    computed: {
        maskedSsn(): string {
            return this.nurse.ssn ? maskSsn(this.nurse.ssn) : '—'
        },
    },
})
</script>

<template>
    <BaseTableRow is-selectable @click="$emit('select', nurse)">
        <BaseTableCell class="font-mono">
            {{ nurse.nurseID }}
        </BaseTableCell>
        <BaseTableCell class="font-medium">
            {{ nurse.name }}
        </BaseTableCell>
        <BaseTableCell>{{ nurse.position }}</BaseTableCell>
        <BaseTableCell class="font-mono text-neutral-500 dark:text-neutral-400">
            {{ maskedSsn }}
        </BaseTableCell>
    </BaseTableRow>
</template>
