<script lang="ts">
import type { PropType } from 'vue'
import { defineComponent } from 'vue'
import BaseTableCell from '@/components/base/BaseTableCell.vue'
import BaseTableRow from '@/components/base/BaseTableRow.vue'
import { maskSsn } from '@/lib/maskSsn'
import type { Physician } from '@/types'

export default defineComponent({
    name: 'PhysiciansTableRow',

    components: {
        BaseTableCell,
        BaseTableRow,
    },

    props: {
        physician: {
            type: Object as PropType<Physician>,
            required: true,
        },
    },

    emits: ['select'],

    computed: {
        maskedSsn(): string {
            return this.physician.ssn ? maskSsn(this.physician.ssn) : '—'
        },
    },
})
</script>

<template>
    <BaseTableRow is-selectable @click="$emit('select', physician)">
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
    </BaseTableRow>
</template>
