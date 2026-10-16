<script lang="ts">
import { Pencil, UserX } from '@lucide/vue'
import type { PropType } from 'vue'
import { defineComponent } from 'vue'
import BaseTableActionButton from '@/components/base/BaseTableActionButton.vue'
import BaseTableCell from '@/components/base/BaseTableCell.vue'
import BaseTableRow from '@/components/base/BaseTableRow.vue'
import PatientNameLink from '@/components/patient/PatientNameLink.vue'
import PhysicianNameLink from '@/components/staff/PhysicianNameLink.vue'
import { maskSsn } from '@/lib/maskSsn'
import type { Patient } from '@/types'

export default defineComponent({
    name: 'PatientsTableRow',

    components: {
        BaseTableActionButton,
        BaseTableCell,
        BaseTableRow,
        Pencil,
        UserX,
        PhysicianNameLink,
        PatientNameLink,
    },

    props: {
        patient: {
            type: Object as PropType<Patient>,
            required: true,
        },
    },

    emits: ['edit', 'deactivate'],

    computed: {
        dateOfBirth(): string {
            if (!this.patient.dob) {
                return '—'
            }

            return `${this.patient.dob} (${this.patient.age})`
        },

        maskedSsn(): string {
            return this.patient.ssn ? maskSsn(this.patient.ssn) : '—'
        },
    },
})
</script>

<template>
    <BaseTableRow>
        <BaseTableCell align="center" class="font-mono">
            {{ patient.patientID }}
        </BaseTableCell>
        <BaseTableCell>
            <PatientNameLink :patient="patient" />
        </BaseTableCell>
        <BaseTableCell
            align="center"
            class="tabular-nums"
            :class="{ 'text-neutral-500 dark:text-neutral-400': !patient.dob }"
        >
            {{ dateOfBirth }}
        </BaseTableCell>
        <BaseTableCell
            align="center"
            class="whitespace-nowrap"
            :class="{
                'text-neutral-500 dark:text-neutral-400': !patient.phone,
            }"
        >
            {{ patient.phone ?? '—' }}
        </BaseTableCell>
        <BaseTableCell
            align="center"
            class="font-mono"
            :class="{
                'text-neutral-500 dark:text-neutral-400':
                    !patient.insuranceNumber,
            }"
        >
            {{ patient.insuranceNumber ?? '—' }}
        </BaseTableCell>
        <BaseTableCell>
            <PhysicianNameLink
                v-if="patient.primary_physician"
                :physician="patient.primary_physician"
            />
            <span v-else class="text-neutral-500 dark:text-neutral-400">
                —
            </span>
        </BaseTableCell>
        <BaseTableCell
            align="center"
            class="font-mono text-neutral-500 dark:text-neutral-400"
        >
            {{ maskedSsn }}
        </BaseTableCell>
        <BaseTableCell align="right" class="w-0">
            <div class="flex items-center justify-end gap-1">
                <BaseTableActionButton
                    tone="brand"
                    @click.stop="$emit('edit', patient)"
                >
                    <Pencil />
                    Edit
                </BaseTableActionButton>
                <BaseTableActionButton
                    tone="danger"
                    @click.stop="$emit('deactivate', patient)"
                >
                    <UserX />
                    Deactivate
                </BaseTableActionButton>
            </div>
        </BaseTableCell>
    </BaseTableRow>
</template>
