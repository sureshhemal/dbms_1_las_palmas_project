<script lang="ts">
import type { PropType } from 'vue'
import { defineComponent } from 'vue'
import BaseTable from '@/components/base/BaseTable.vue'
import PatientPerformedProceduresTableRow from '@/components/patient/PatientPerformedProceduresTableRow.vue'
import type { PatientPerformedProcedure } from '@/types'

export default defineComponent({
    name: 'PatientPerformedProceduresTable',

    components: {
        BaseTable,
        PatientPerformedProceduresTableRow,
    },

    props: {
        performedProcedures: {
            type: Array as PropType<PatientPerformedProcedure[]>,
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
            columnHeadings: ['Date', 'Procedure', 'Room', 'Physician', 'Nurse'],
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
        <PatientPerformedProceduresTableRow
            v-for="performedProcedure in performedProcedures"
            :key="performedProcedure.id"
            :performed-procedure="performedProcedure"
        />
    </BaseTable>
</template>
