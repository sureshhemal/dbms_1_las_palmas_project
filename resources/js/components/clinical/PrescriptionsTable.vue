<script lang="ts">
import type { PropType } from 'vue'
import { defineComponent } from 'vue'
import BaseTable from '@/components/base/BaseTable.vue'
import PrescriptionsTableRow from '@/components/clinical/PrescriptionsTableRow.vue'
import type { Prescription } from '@/types'

export default defineComponent({
    name: 'PrescriptionsTable',

    components: {
        BaseTable,
        PrescriptionsTableRow,
    },

    props: {
        prescriptions: {
            type: Array as PropType<Prescription[]>,
            required: true,
        },
        isLoading: {
            type: Boolean,
            default: false,
        },
        emptyMessage: {
            type: String,
            default: 'No prescriptions yet.',
        },
    },

    data() {
        return {
            columnHeadings: [
                'Prescribed',
                'Patient',
                'Medication',
                'Dose',
                'Prescriber',
            ],
        }
    },
})
</script>

<template>
    <BaseTable
        :column-headings="columnHeadings"
        :is-loading="isLoading"
        :is-empty="prescriptions.length === 0"
        :empty-message="emptyMessage"
    >
        <PrescriptionsTableRow
            v-for="prescription in prescriptions"
            :key="prescription.id"
            :prescription="prescription"
        />
    </BaseTable>
</template>
