<script lang="ts">
import type { PropType } from 'vue'
import { defineComponent } from 'vue'
import BaseTableCell from '@/components/base/BaseTableCell.vue'
import BaseTableRow from '@/components/base/BaseTableRow.vue'
import type { Prescription } from '@/types'

export default defineComponent({
    name: 'PrescriptionsTableRow',

    components: {
        BaseTableCell,
        BaseTableRow,
    },

    props: {
        prescription: {
            type: Object as PropType<Prescription>,
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
        <BaseTableCell
            :class="{
                'text-neutral-500 line-through dark:text-neutral-400':
                    prescription.patient.deleted_at,
            }"
            :title="prescription.patient.deleted_at ? 'Inactive' : undefined"
        >
            <span class="font-medium">{{ prescription.patient.name }}</span>
            <span class="ml-1 font-mono text-neutral-500 dark:text-neutral-400">
                {{ prescription.patient.patientID }}
            </span>
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
        <BaseTableCell
            :class="{
                'text-neutral-500 line-through dark:text-neutral-400':
                    prescription.physician.deleted_at,
            }"
            :title="
                prescription.physician.deleted_at ? 'Offboarded' : undefined
            "
        >
            {{ prescription.physician.name }}
        </BaseTableCell>
    </BaseTableRow>
</template>
