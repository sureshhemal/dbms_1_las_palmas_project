<script lang="ts">
import type { PropType } from 'vue'
import { defineComponent } from 'vue'
import BaseTable from '@/components/base/BaseTable.vue'
import TodayProceduresTableRow from '@/components/dashboard/TodayProceduresTableRow.vue'
import type { ColumnAlignment, TodayProcedure } from '@/types'

export default defineComponent({
    name: 'TodayProceduresTable',

    components: {
        BaseTable,
        TodayProceduresTableRow,
    },

    props: {
        procedures: {
            type: Array as PropType<TodayProcedure[]>,
            required: true,
        },
        isLoading: {
            type: Boolean,
            default: false,
        },
    },

    data() {
        return {
            columnHeadings: [
                'Patient',
                'Procedure',
                'Room',
                'Physician',
                'Nurse',
            ],
            columnAlignments: {
                Room: 'center',
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
        :is-empty="procedures.length === 0"
        empty-message="No procedures scheduled today."
    >
        <TodayProceduresTableRow
            v-for="performedProcedure in procedures"
            :key="performedProcedure.id"
            :performed-procedure="performedProcedure"
        />
    </BaseTable>
</template>
