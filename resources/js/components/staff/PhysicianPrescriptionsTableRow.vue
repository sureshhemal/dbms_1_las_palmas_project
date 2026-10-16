<script lang="ts">
import type { PropType } from 'vue'
import { defineComponent } from 'vue'
import BaseTableCell from '@/components/base/BaseTableCell.vue'
import BaseTableRow from '@/components/base/BaseTableRow.vue'
import PatientNameLink from '@/components/patient/PatientNameLink.vue'
import type { PhysicianPrescription } from '@/types'

export default defineComponent({
    name: 'PhysicianPrescriptionsTableRow',

    components: {
        BaseTableCell,
        BaseTableRow,
        PatientNameLink,
    },

    props: {
        prescription: {
            type: Object as PropType<PhysicianPrescription>,
            required: true,
        },
    },
})
</script>

<template>
    <BaseTableRow>
        <BaseTableCell class="whitespace-nowrap tabular-nums">
            {{ prescription.prescribedDate }}
        </BaseTableCell>
        <BaseTableCell>
            <PatientNameLink :patient="prescription.patient" />
        </BaseTableCell>
        <BaseTableCell
            :class="{
                'text-neutral-500 line-through dark:text-neutral-400':
                    prescription.medication.deleted_at,
            }"
            :title="prescription.medication.deleted_at ? 'Removed' : undefined"
        >
            {{ prescription.medication.name }}
        </BaseTableCell>
        <BaseTableCell>{{ prescription.dose }}</BaseTableCell>
    </BaseTableRow>
</template>
