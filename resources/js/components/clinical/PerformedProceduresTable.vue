<script lang="ts">
import type { PropType } from 'vue'
import { defineComponent } from 'vue'
import BaseTable from '@/components/base/BaseTable.vue'
import PerformedProceduresTableRow from '@/components/clinical/PerformedProceduresTableRow.vue'
import type { PerformedProcedure } from '@/types'

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
    >
        <PerformedProceduresTableRow
            v-for="performedProcedure in performedProcedures"
            :key="performedProcedure.id"
            :performed-procedure="performedProcedure"
            :today="today"
        />
    </BaseTable>
</template>
