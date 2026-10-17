<script lang="ts">
import type { PropType } from 'vue'
import { defineComponent } from 'vue'
import BaseTable from '@/components/base/BaseTable.vue'
import AppointmentsTableRow from '@/components/clinical/AppointmentsTableRow.vue'
import type { Appointment, ColumnAlignment } from '@/types'

export default defineComponent({
    name: 'AppointmentsTable',

    components: {
        AppointmentsTableRow,
        BaseTable,
    },

    props: {
        appointments: {
            type: Array as PropType<Appointment[]>,
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
        emptyMessage: {
            type: String,
            default: 'No appointments yet.',
        },
    },

    emits: ['edit', 'cancel'],

    data() {
        return {
            columnHeadings: [
                'App ID',
                'Date',
                'Time',
                'Status',
                'Patient',
                'Physician',
                'Nurse',
                '',
            ],
            columnAlignments: {
                'App ID': 'center',
                Date: 'center',
                Time: 'center',
                Status: 'center',
                '': 'right',
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
        :empty-message="emptyMessage"
    >
        <AppointmentsTableRow
            v-for="appointment in appointments"
            :key="appointment.id"
            :appointment="appointment"
            :now="now"
            @edit="$emit('edit', $event)"
            @cancel="$emit('cancel', $event)"
        />
    </BaseTable>
</template>
