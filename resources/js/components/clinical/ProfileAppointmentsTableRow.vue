<script lang="ts">
import type { PropType } from 'vue'
import { defineComponent } from 'vue'
import BaseTableCell from '@/components/base/BaseTableCell.vue'
import BaseTableRow from '@/components/base/BaseTableRow.vue'
import PatientNameLink from '@/components/patient/PatientNameLink.vue'
import PhysicianNameLink from '@/components/staff/PhysicianNameLink.vue'
import {
    appointmentStatusClasses,
    appointmentStatusLabels,
    appointmentStatusOf,
} from '@/lib/appointmentStatus'
import { hospitalDateOf, hospitalTimeOf } from '@/lib/hospitalTime'
import type { Appointment, AppointmentStatus } from '@/types'

export default defineComponent({
    name: 'ProfileAppointmentsTableRow',

    components: {
        BaseTableCell,
        BaseTableRow,
        PatientNameLink,
        PhysicianNameLink,
    },

    props: {
        appointment: {
            type: Object as PropType<Appointment>,
            required: true,
        },
        counterpart: {
            type: String as PropType<'patient' | 'physician'>,
            required: true,
        },
        now: {
            type: String,
            required: true,
        },
    },

    computed: {
        timeZone(): string {
            return this.$page.props.hospitalTimezone
        },

        date(): string {
            return hospitalDateOf(this.appointment.startDateTime, this.timeZone)
        },

        timeRange(): string {
            return `${hospitalTimeOf(this.appointment.startDateTime, this.timeZone)} – ${hospitalTimeOf(this.appointment.endDateTime, this.timeZone)}`
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
            {{ date }}
        </BaseTableCell>
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
            <PhysicianNameLink
                v-if="counterpart === 'physician'"
                :physician="appointment.physician"
            />
            <PatientNameLink v-else :patient="appointment.patient" />
        </BaseTableCell>
        <BaseTableCell
            :class="{
                'text-neutral-500 line-through dark:text-neutral-400':
                    appointment.nurse.deleted_at,
            }"
            :title="appointment.nurse.deleted_at ? 'Offboarded' : undefined"
        >
            {{ appointment.nurse.name }}
        </BaseTableCell>
    </BaseTableRow>
</template>
