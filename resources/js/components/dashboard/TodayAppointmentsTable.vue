<script lang="ts">
import type { PropType } from 'vue'
import { defineComponent } from 'vue'
import BaseTable from '@/components/base/BaseTable.vue'
import TodayAppointmentsTableRow from '@/components/dashboard/TodayAppointmentsTableRow.vue'
import type { ColumnAlignment, TodayAppointment } from '@/types'

export default defineComponent({
    name: 'TodayAppointmentsTable',

    components: {
        BaseTable,
        TodayAppointmentsTableRow,
    },

    props: {
        appointments: {
            type: Array as PropType<TodayAppointment[]>,
            required: true,
        },
        now: {
            type: String,
            required: true,
        },
        isLoading: {
            type: Boolean,
            default: false,
        },
    },

    data() {
        return {
            columnHeadings: ['Time', 'Status', 'Patient', 'Physician', 'Nurse'],
            columnAlignments: {
                Time: 'center',
                Status: 'center',
            } as Record<string, ColumnAlignment>,
        }
    },
})
</script>

<template>
    <BaseTable
        :column-headings="columnHeadings"
        :column-alignments="columnAlignments"
        :is-loading="isLoading"
        :is-empty="appointments.length === 0"
        empty-message="No appointments today."
    >
        <TodayAppointmentsTableRow
            v-for="appointment in appointments"
            :key="appointment.id"
            :appointment="appointment"
            :now="now"
        />
    </BaseTable>
</template>
