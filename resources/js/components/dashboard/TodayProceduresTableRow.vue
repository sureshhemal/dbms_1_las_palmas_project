<script lang="ts">
import type { PropType } from 'vue'
import { defineComponent } from 'vue'
import BaseTableCell from '@/components/base/BaseTableCell.vue'
import BaseTableRow from '@/components/base/BaseTableRow.vue'
import PatientContact from '@/components/patient/PatientContact.vue'
import PhysicianNameLink from '@/components/staff/PhysicianNameLink.vue'
import type { TodayProcedure } from '@/types'

export default defineComponent({
    name: 'TodayProceduresTableRow',

    components: {
        BaseTableCell,
        BaseTableRow,
        PatientContact,
        PhysicianNameLink,
    },

    props: {
        performedProcedure: {
            type: Object as PropType<TodayProcedure>,
            required: true,
        },
    },
})
</script>

<template>
    <BaseTableRow>
        <BaseTableCell>
            <PatientContact :patient="performedProcedure.patient" />
        </BaseTableCell>
        <BaseTableCell class="font-medium">
            {{ performedProcedure.procedure.name }}
        </BaseTableCell>
        <BaseTableCell align="center" class="whitespace-nowrap">
            Room {{ performedProcedure.stay.room.roomID }}
        </BaseTableCell>
        <BaseTableCell>
            <PhysicianNameLink :physician="performedProcedure.physician" />
        </BaseTableCell>
        <BaseTableCell
            :class="{
                'text-neutral-500 line-through dark:text-neutral-400':
                    performedProcedure.nurse.deleted_at,
            }"
        >
            {{ performedProcedure.nurse.name }}
        </BaseTableCell>
    </BaseTableRow>
</template>
