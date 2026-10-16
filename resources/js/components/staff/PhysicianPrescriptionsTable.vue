<script lang="ts">
import type { PropType } from 'vue'
import { defineComponent } from 'vue'
import BaseTable from '@/components/base/BaseTable.vue'
import PhysicianPrescriptionsTableRow from '@/components/staff/PhysicianPrescriptionsTableRow.vue'
import type { PhysicianPrescription } from '@/types'

export default defineComponent({
    name: 'PhysicianPrescriptionsTable',

    components: {
        BaseTable,
        PhysicianPrescriptionsTableRow,
    },

    props: {
        prescriptions: {
            type: Array as PropType<PhysicianPrescription[]>,
            required: true,
        },
        isLoading: {
            type: Boolean,
            default: false,
        },
    },

    data() {
        return {
            columnHeadings: ['Prescribed', 'Patient', 'Medication', 'Dose'],
        }
    },
})
</script>

<template>
    <BaseTable
        :column-headings="columnHeadings"
        :is-loading="isLoading"
        :is-empty="prescriptions.length === 0"
        empty-message="No prescriptions issued yet."
    >
        <PhysicianPrescriptionsTableRow
            v-for="prescription in prescriptions"
            :key="prescription.id"
            :prescription="prescription"
        />
    </BaseTable>
</template>
