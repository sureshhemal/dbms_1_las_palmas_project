<script lang="ts">
import type { PropType } from 'vue'
import { defineComponent } from 'vue'
import BaseTableCell from '@/components/base/BaseTableCell.vue'
import BaseTableRow from '@/components/base/BaseTableRow.vue'
import PhysicianNameLink from '@/components/staff/PhysicianNameLink.vue'
import PatientNameLink from '@/components/patient/PatientNameLink.vue'
import type { PerformedProcedure } from '@/types'

export default defineComponent({
    name: 'PerformedProceduresTableRow',

    components: {
        BaseTableCell,
        BaseTableRow,
        PatientNameLink,
        PhysicianNameLink,
    },

    props: {
        performedProcedure: {
            type: Object as PropType<PerformedProcedure>,
            required: true,
        },
        today: {
            type: String,
            required: true,
        },
    },

    computed: {
        isScheduled(): boolean {
            return this.performedProcedure.procDate > this.today
        },
    },
})
</script>

<template>
    <BaseTableRow>
        <BaseTableCell align="center" class="whitespace-nowrap tabular-nums">
            {{ performedProcedure.procDate }}
        </BaseTableCell>
        <BaseTableCell align="center">
            <span
                class="rounded-full px-2 py-0.5 text-xs font-medium"
                :class="
                    isScheduled
                        ? 'bg-info-100 text-info-800 dark:bg-info-950 dark:text-info-300'
                        : 'bg-success-100 text-success-800 dark:bg-success-950 dark:text-success-300'
                "
            >
                {{ isScheduled ? 'Scheduled' : 'Performed' }}
            </span>
        </BaseTableCell>
        <BaseTableCell>
            <PatientNameLink :patient="performedProcedure.patient" />
        </BaseTableCell>
        <BaseTableCell>{{ performedProcedure.procedure.name }}</BaseTableCell>
        <BaseTableCell align="center" class="whitespace-nowrap">
            Room {{ performedProcedure.stay.room.roomID }}
        </BaseTableCell>
        <BaseTableCell>
            <PhysicianNameLink :physician="performedProcedure.physician" />
        </BaseTableCell>
        <BaseTableCell>{{ performedProcedure.nurse.name }}</BaseTableCell>
    </BaseTableRow>
</template>
