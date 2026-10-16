<script lang="ts">
import type { PropType } from 'vue'
import { defineComponent } from 'vue'
import BaseTableCell from '@/components/base/BaseTableCell.vue'
import BaseTableRow from '@/components/base/BaseTableRow.vue'
import PatientNameLink from '@/components/patient/PatientNameLink.vue'
import type { PhysicianPerformedProcedure } from '@/types'

export default defineComponent({
    name: 'PhysicianProceduresTableRow',

    components: {
        BaseTableCell,
        BaseTableRow,
        PatientNameLink,
    },

    props: {
        performedProcedure: {
            type: Object as PropType<PhysicianPerformedProcedure>,
            required: true,
        },
    },
})
</script>

<template>
    <BaseTableRow>
        <BaseTableCell class="whitespace-nowrap tabular-nums">
            {{ performedProcedure.procDate }}
        </BaseTableCell>
        <BaseTableCell>
            <PatientNameLink :patient="performedProcedure.patient" />
        </BaseTableCell>
        <BaseTableCell>{{ performedProcedure.procedure.name }}</BaseTableCell>
        <BaseTableCell class="whitespace-nowrap">
            Room {{ performedProcedure.stay.room.roomID }}
        </BaseTableCell>
        <BaseTableCell>{{ performedProcedure.nurse.name }}</BaseTableCell>
    </BaseTableRow>
</template>
