<script lang="ts">
import { Pencil, Trash2 } from '@lucide/vue'
import type { PropType } from 'vue'
import { defineComponent } from 'vue'
import BaseTableActionButton from '@/components/base/BaseTableActionButton.vue'
import BaseTableCell from '@/components/base/BaseTableCell.vue'
import BaseTableRow from '@/components/base/BaseTableRow.vue'
import { maskSsn } from '@/lib/maskSsn'
import type { Patient } from '@/types'

export default defineComponent({
    name: 'PatientsTableRow',

    components: {
        BaseTableActionButton,
        BaseTableCell,
        BaseTableRow,
        Pencil,
        Trash2,
    },

    props: {
        patient: {
            type: Object as PropType<Patient>,
            required: true,
        },
    },

    emits: ['edit', 'remove'],

    computed: {
        dateOfBirth(): string {
            if (!this.patient.dob) {
                return '—'
            }

            return `${this.patient.dob} (${this.patient.age})`
        },

        primaryPhysicianName(): string {
            return this.patient.primary_physician?.name ?? '—'
        },

        isPrimaryPhysicianOffboarded(): boolean {
            return Boolean(this.patient.primary_physician?.deleted_at)
        },

        maskedSsn(): string {
            return this.patient.ssn ? maskSsn(this.patient.ssn) : '—'
        },
    },
})
</script>

<template>
    <BaseTableRow>
        <BaseTableCell class="font-mono">
            {{ patient.patientID }}
        </BaseTableCell>
        <BaseTableCell class="font-medium">
            {{ patient.name }}
        </BaseTableCell>
        <BaseTableCell
            class="tabular-nums"
            :class="{ 'text-neutral-500 dark:text-neutral-400': !patient.dob }"
        >
            {{ dateOfBirth }}
        </BaseTableCell>
        <BaseTableCell
            class="whitespace-nowrap"
            :class="{
                'text-neutral-500 dark:text-neutral-400': !patient.phone,
            }"
        >
            {{ patient.phone ?? '—' }}
        </BaseTableCell>
        <BaseTableCell
            class="font-mono"
            :class="{
                'text-neutral-500 dark:text-neutral-400':
                    !patient.insuranceNumber,
            }"
        >
            {{ patient.insuranceNumber ?? '—' }}
        </BaseTableCell>
        <BaseTableCell
            :class="{
                'text-neutral-500 dark:text-neutral-400':
                    !patient.primary_physician || isPrimaryPhysicianOffboarded,
                'line-through': isPrimaryPhysicianOffboarded,
            }"
            :title="isPrimaryPhysicianOffboarded ? 'Offboarded' : undefined"
        >
            {{ primaryPhysicianName }}
        </BaseTableCell>
        <BaseTableCell class="font-mono text-neutral-500 dark:text-neutral-400">
            {{ maskedSsn }}
        </BaseTableCell>
        <BaseTableCell class="w-0 text-right">
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
                    @click.stop="$emit('remove', patient)"
                >
                    <Trash2 />
                    Remove
                </BaseTableActionButton>
            </div>
        </BaseTableCell>
    </BaseTableRow>
</template>
