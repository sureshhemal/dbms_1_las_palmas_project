<script lang="ts">
import { CalendarX2, Pencil } from '@lucide/vue'
import type { PropType } from 'vue'
import { defineComponent } from 'vue'
import BaseTableActionButton from '@/components/base/BaseTableActionButton.vue'
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
    name: 'AppointmentsTableRow',

    components: {
        BaseTableActionButton,
        BaseTableCell,
        BaseTableRow,
        CalendarX2,
        PatientNameLink,
        Pencil,
        PhysicianNameLink,
    },

    props: {
        appointment: {
            type: Object as PropType<Appointment>,
            required: true,
        },
        now: {
            type: String,
            required: true,
        },
    },

    emits: ['edit', 'cancel'],

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
        <BaseTableCell align="center" class="font-mono">
            {{ appointment.appID }}
        </BaseTableCell>
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
            <PatientNameLink :patient="appointment.patient" />
        </BaseTableCell>
        <BaseTableCell>
            <PhysicianNameLink :physician="appointment.physician" />
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
        <BaseTableCell align="right" class="w-0">
            <div class="flex items-center justify-end gap-1">
                <BaseTableActionButton
                    tone="brand"
                    @click.stop="$emit('edit', appointment)"
                >
                    <Pencil />
                    Edit
                </BaseTableActionButton>
                <BaseTableActionButton
                    v-if="status === 'upcoming'"
                    tone="danger"
                    @click.stop="$emit('cancel', appointment)"
                >
                    <CalendarX2 />
                    Cancel
                </BaseTableActionButton>
            </div>
        </BaseTableCell>
    </BaseTableRow>
</template>
