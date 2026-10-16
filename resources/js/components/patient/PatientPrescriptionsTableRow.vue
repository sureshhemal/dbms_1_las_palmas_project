<script lang="ts">
import type { PropType } from 'vue'
import { defineComponent } from 'vue'
import BaseTableCell from '@/components/base/BaseTableCell.vue'
import BaseTableRow from '@/components/base/BaseTableRow.vue'
import PhysicianNameLink from '@/components/staff/PhysicianNameLink.vue'
import type { PatientPrescription } from '@/types'

export default defineComponent({
    name: 'PatientPrescriptionsTableRow',

    components: {
        BaseTableCell,
        BaseTableRow,
        PhysicianNameLink,
    },

    props: {
        prescription: {
            type: Object as PropType<PatientPrescription>,
            required: true,
        },
    },
})
</script>

<template>
    <BaseTableRow>
        <BaseTableCell align="center" class="whitespace-nowrap tabular-nums">
            {{ prescription.prescribedDate }}
        </BaseTableCell>
        <BaseTableCell
            class="font-medium"
            :class="{
                'text-neutral-500 line-through dark:text-neutral-400':
                    prescription.medication.deleted_at,
            }"
            :title="prescription.medication.deleted_at ? 'Removed' : undefined"
        >
            {{ prescription.medication.name }}
        </BaseTableCell>
        <BaseTableCell>{{ prescription.dose }}</BaseTableCell>
        <BaseTableCell>
            <PhysicianNameLink :physician="prescription.physician" />
        </BaseTableCell>
    </BaseTableRow>
</template>
