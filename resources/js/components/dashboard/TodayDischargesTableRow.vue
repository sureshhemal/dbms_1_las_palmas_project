<script lang="ts">
import type { PropType } from 'vue'
import { defineComponent } from 'vue'
import BaseTableCell from '@/components/base/BaseTableCell.vue'
import BaseTableRow from '@/components/base/BaseTableRow.vue'
import PatientContact from '@/components/patient/PatientContact.vue'
import type { TodayDischarge } from '@/types'

const millisecondsPerDay = 24 * 60 * 60 * 1000

export default defineComponent({
    name: 'TodayDischargesTableRow',

    components: {
        BaseTableCell,
        BaseTableRow,
        PatientContact,
    },

    props: {
        discharge: {
            type: Object as PropType<TodayDischarge>,
            required: true,
        },
    },

    computed: {
        lengthOfStay(): string {
            const days = Math.round(
                (Date.parse(`${this.discharge.endDate}T00:00:00Z`) -
                    Date.parse(`${this.discharge.startDate}T00:00:00Z`)) /
                    millisecondsPerDay,
            )

            return `${days} ${days === 1 ? 'day' : 'days'}`
        },
    },
})
</script>

<template>
    <BaseTableRow>
        <BaseTableCell>
            <PatientContact :patient="discharge.patient" />
        </BaseTableCell>
        <BaseTableCell align="center" class="whitespace-nowrap">
            Room {{ discharge.room.roomID }}
        </BaseTableCell>
        <BaseTableCell align="center" class="tabular-nums">
            {{ discharge.startDate }}
        </BaseTableCell>
        <BaseTableCell align="center" class="tabular-nums">
            {{ lengthOfStay }}
        </BaseTableCell>
    </BaseTableRow>
</template>
