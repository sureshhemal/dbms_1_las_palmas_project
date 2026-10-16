<script lang="ts">
import type { PropType } from 'vue'
import { defineComponent } from 'vue'
import BaseTableCell from '@/components/base/BaseTableCell.vue'
import BaseTableRow from '@/components/base/BaseTableRow.vue'
import PhysicianNameLink from '@/components/staff/PhysicianNameLink.vue'
import type { PatientPerformedProcedure } from '@/types'

export default defineComponent({
    name: 'PatientPerformedProceduresTableRow',

    components: {
        BaseTableCell,
        BaseTableRow,
        PhysicianNameLink,
    },

    props: {
        performedProcedure: {
            type: Object as PropType<PatientPerformedProcedure>,
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
        <BaseTableCell class="font-medium">
            {{ performedProcedure.procedure.name }}
        </BaseTableCell>
        <BaseTableCell class="whitespace-nowrap">
            Room {{ performedProcedure.stay.room.roomID }}
        </BaseTableCell>
        <BaseTableCell>
            <PhysicianNameLink :physician="performedProcedure.physician" />
        </BaseTableCell>
        <BaseTableCell>{{ performedProcedure.nurse.name }}</BaseTableCell>
    </BaseTableRow>
</template>
