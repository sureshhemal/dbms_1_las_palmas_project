<script lang="ts">
import type { PropType } from 'vue'
import { defineComponent } from 'vue'
import BaseTable from '@/components/base/BaseTable.vue'
import PerformedProceduresTableRow from '@/components/clinical/PerformedProceduresTableRow.vue'
import type { ColumnAlignment, PerformedProcedure } from '@/types'

export default defineComponent({
    name: 'PerformedProceduresTable',

    components: {
        BaseTable,
        PerformedProceduresTableRow,
    },

    props: {
        performedProcedures: {
            type: Array as PropType<PerformedProcedure[]>,
            required: true,
        },
        today: {
            type: String,
            required: true,
        },
        isLoading: {
            type: Boolean,
            default: false,
        },
        emptyMessage: {
            type: String,
            default: 'No procedures performed yet.',
        },
    },

    data() {
        return {
            columnHeadings: [
                'Date',
                'Status',
                'Patient',
                'Procedure',
                'Room',
                'Physician',
                'Nurse',
            ],
            columnAlignments: {
                Date: 'center',
                Status: 'center',
                Room: 'center',
            } as Record<string, ColumnAlignment>,
        }
    },
})
</script>

<template>
    <BaseTable
        :column-headings="columnHeadings"
        :is-loading="isLoading"
        :is-empty="performedProcedures.length === 0"
        :empty-message="emptyMessage"
        :column-alignments="columnAlignments"
    >
        <PerformedProceduresTableRow
            v-for="performedProcedure in performedProcedures"
            :key="performedProcedure.id"
            :performed-procedure="performedProcedure"
            :today="today"
        />
    </BaseTable>
</template>
