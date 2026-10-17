<script lang="ts">
import type { PropType } from 'vue'
import { defineComponent } from 'vue'
import BaseTable from '@/components/base/BaseTable.vue'
import OnCallNursesTableRow from '@/components/dashboard/OnCallNursesTableRow.vue'
import type { ColumnAlignment, TodayOnCall } from '@/types'

export default defineComponent({
    name: 'OnCallNursesTable',

    components: {
        BaseTable,
        OnCallNursesTableRow,
    },

    props: {
        onCallNurses: {
            type: Array as PropType<TodayOnCall[]>,
            required: true,
        },
        isLoading: {
            type: Boolean,
            default: false,
        },
    },

    data() {
        return {
            columnHeadings: ['Nurse', 'Position', 'On Call'],
            columnAlignments: {
                'On Call': 'center',
            } as Record<string, ColumnAlignment>,
        }
    },
})
</script>

<template>
    <BaseTable
        :column-headings="columnHeadings"
        :column-alignments="columnAlignments"
        :is-loading="isLoading"
        :is-empty="onCallNurses.length === 0"
        empty-message="No nurses on call today."
    >
        <OnCallNursesTableRow
            v-for="onCall in onCallNurses"
            :key="onCall.id"
            :on-call="onCall"
        />
    </BaseTable>
</template>
