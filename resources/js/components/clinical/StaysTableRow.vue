<script lang="ts">
import { CalendarPlus, LogOut, Stethoscope } from '@lucide/vue'
import type { PropType } from 'vue'
import { defineComponent } from 'vue'
import BaseTableActionButton from '@/components/base/BaseTableActionButton.vue'
import BaseTableCell from '@/components/base/BaseTableCell.vue'
import BaseTableRow from '@/components/base/BaseTableRow.vue'
import PatientNameLink from '@/components/patient/PatientNameLink.vue'
import type { Stay } from '@/types'

export default defineComponent({
    name: 'StaysTableRow',

    components: {
        BaseTableActionButton,
        BaseTableCell,
        BaseTableRow,
        PatientNameLink,
        CalendarPlus,
        LogOut,
        Stethoscope,
    },

    props: {
        stay: {
            type: Object as PropType<Stay>,
            required: true,
        },
        today: {
            type: String,
            required: true,
        },
    },

    emits: ['add-procedure', 'extend', 'discharge'],

    computed: {
        isOver(): boolean {
            return this.stay.endDate !== null && this.stay.endDate < this.today
        },

        isScheduled(): boolean {
            return this.stay.startDate > this.today
        },

        status(): string {
            if (this.isOver) {
                return 'Discharged'
            }

            return this.isScheduled ? 'Scheduled' : 'Admitted'
        },

        statusClasses(): string {
            if (this.isOver) {
                return 'bg-neutral-100 text-neutral-700 dark:bg-neutral-800 dark:text-neutral-300'
            }

            return this.isScheduled
                ? 'bg-info-100 text-info-800 dark:bg-info-950 dark:text-info-300'
                : 'bg-success-100 text-success-800 dark:bg-success-950 dark:text-success-300'
        },
    },
})
</script>

<template>
    <BaseTableRow>
        <BaseTableCell align="center" class="font-mono">
            {{ stay.stayID }}
        </BaseTableCell>
        <BaseTableCell>
            <PatientNameLink :patient="stay.patient" />
        </BaseTableCell>
        <BaseTableCell
            align="center"
            :class="{
                'text-neutral-500 line-through dark:text-neutral-400':
                    stay.room.deleted_at,
            }"
            :title="stay.room.deleted_at ? 'Decommissioned' : undefined"
        >
            <span class="font-mono">{{ stay.room.roomID }}</span>
            <span class="ml-1 text-neutral-500 dark:text-neutral-400">
                {{ stay.room.roomType }}
            </span>
        </BaseTableCell>
        <BaseTableCell align="center" class="whitespace-nowrap tabular-nums">
            {{ stay.startDate }}
        </BaseTableCell>
        <BaseTableCell
            align="center"
            class="whitespace-nowrap tabular-nums"
            :class="{
                'text-neutral-500 dark:text-neutral-400': !stay.endDate,
            }"
        >
            {{ stay.endDate ?? 'Not decided' }}
        </BaseTableCell>
        <BaseTableCell align="center">
            <span
                class="rounded-full px-2 py-0.5 text-xs font-medium"
                :class="statusClasses"
            >
                {{ status }}
            </span>
        </BaseTableCell>
        <BaseTableCell align="right" class="w-0">
            <div v-if="!isOver" class="flex items-center justify-end gap-1">
                <BaseTableActionButton
                    tone="brand"
                    @click.stop="$emit('add-procedure', stay)"
                >
                    <Stethoscope />
                    Add Procedure
                </BaseTableActionButton>
                <BaseTableActionButton
                    v-if="stay.endDate"
                    tone="brand"
                    @click.stop="$emit('extend', stay)"
                >
                    <CalendarPlus />
                    Extend
                </BaseTableActionButton>
                <BaseTableActionButton
                    tone="brand"
                    @click.stop="$emit('discharge', stay)"
                >
                    <LogOut />
                    Discharge
                </BaseTableActionButton>
            </div>
        </BaseTableCell>
    </BaseTableRow>
</template>
