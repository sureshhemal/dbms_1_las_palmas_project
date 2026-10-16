<script lang="ts">
import type { PropType } from 'vue'
import { defineComponent } from 'vue'
import BaseTable from '@/components/base/BaseTable.vue'
import PatientPrescriptionsTableRow from '@/components/patient/PatientPrescriptionsTableRow.vue'
import type { ColumnAlignment, PatientPrescription } from '@/types'

export default defineComponent({
    name: 'PatientPrescriptionsTable',

    components: {
        BaseTable,
        PatientPrescriptionsTableRow,
    },

    props: {
        prescriptions: {
            type: Array as PropType<PatientPrescription[]>,
            required: true,
        },
        isLoading: {
            type: Boolean,
            default: false,
        },
    },

    data() {
        return {
            columnHeadings: ['Prescribed', 'Medication', 'Dose', 'Prescriber'],
            columnAlignments: {
                Prescribed: 'center',
            } as Record<string, ColumnAlignment>,
        }
    },
})
</script>

<template>
    <BaseTable
        :column-headings="columnHeadings"
        :is-loading="isLoading"
        :is-empty="prescriptions.length === 0"
        empty-message="No prescriptions yet."
        :column-alignments="columnAlignments"
    >
        <PatientPrescriptionsTableRow
            v-for="prescription in prescriptions"
            :key="prescription.id"
            :prescription="prescription"
        />
    </BaseTable>
</template>
