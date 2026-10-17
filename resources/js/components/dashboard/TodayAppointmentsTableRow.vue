<script lang="ts">
import type { PropType } from 'vue'
import { defineComponent } from 'vue'
import BaseTableCell from '@/components/base/BaseTableCell.vue'
import BaseTableRow from '@/components/base/BaseTableRow.vue'
import PatientContact from '@/components/patient/PatientContact.vue'
import PhysicianNameLink from '@/components/staff/PhysicianNameLink.vue'
import {
    appointmentStatusClasses,
    appointmentStatusLabels,
    appointmentStatusOf,
} from '@/lib/appointmentStatus'
import { hospitalTimeOf } from '@/lib/hospitalTime'
import type { AppointmentStatus, TodayAppointment } from '@/types'

export default defineComponent({
    name: 'TodayAppointmentsTableRow',

    components: {
        BaseTableCell,
        BaseTableRow,
        PatientContact,
        PhysicianNameLink,
    },

    props: {
        appointment: {
            type: Object as PropType<TodayAppointment>,
            required: true,
        },
        now: {
            type: String,
            required: true,
        },
    },

    computed: {
        timeRange(): string {
            const timeZone = this.$page.props.hospitalTimezone

            return `${hospitalTimeOf(this.appointment.startDateTime, timeZone)} – ${hospitalTimeOf(this.appointment.endDateTime, timeZone)}`
        },

        status(): AppointmentStatus {
            return appointmentStatusOf(this.appointment, this.now)
        },

        statusLabel(): string {
            return appointmentStatusLabels[this.status]
        },

        statusClasses(): string {
            return appointmentStatusClasses[this.status]
        },
    },
})
</script>

<template>
    <BaseTableRow>
        <BaseTableCell align="center" class="tabular-nums">
            {{ timeRange }}
        </BaseTableCell>
        <BaseTableCell align="center">
            <span
                class="rounded-full px-2 py-0.5 text-xs font-medium"
                :class="statusClasses"
            >
                {{ statusLabel }}
            </span>
        </BaseTableCell>
        <BaseTableCell>
            <PatientContact :patient="appointment.patient" />
        </BaseTableCell>
        <BaseTableCell>
            <PhysicianNameLink :physician="appointment.physician" />
        </BaseTableCell>
        <BaseTableCell
            :class="{
                'text-neutral-500 line-through dark:text-neutral-400':
                    appointment.nurse.deleted_at,
            }"
        >
            {{ appointment.nurse.name }}
        </BaseTableCell>
    </BaseTableRow>
</template>
