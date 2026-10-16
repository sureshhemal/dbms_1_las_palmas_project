<script lang="ts">
import { LogOut } from '@lucide/vue'
import type { PropType } from 'vue'
import { defineComponent } from 'vue'
import BaseTableActionButton from '@/components/base/BaseTableActionButton.vue'
import BaseTableCell from '@/components/base/BaseTableCell.vue'
import BaseTableRow from '@/components/base/BaseTableRow.vue'
import type { Stay } from '@/types'

export default defineComponent({
    name: 'StaysTableRow',

    components: {
        BaseTableActionButton,
        BaseTableCell,
        BaseTableRow,
        LogOut,
    },

    props: {
        stay: {
            type: Object as PropType<Stay>,
            required: true,
        },
    },

    emits: ['discharge'],

    computed: {
        isCurrent(): boolean {
            return this.stay.endDate === null
        },
    },
})
</script>

<template>
    <BaseTableRow>
        <BaseTableCell class="font-mono">{{ stay.stayID }}</BaseTableCell>
        <BaseTableCell
            :class="{
                'text-neutral-500 line-through dark:text-neutral-400':
                    stay.patient.deleted_at,
            }"
            :title="stay.patient.deleted_at ? 'Inactive' : undefined"
        >
            <span class="font-medium">{{ stay.patient.name }}</span>
            <span class="ml-1 font-mono text-neutral-500 dark:text-neutral-400">
                {{ stay.patient.patientID }}
            </span>
        </BaseTableCell>
        <BaseTableCell
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
        <BaseTableCell class="whitespace-nowrap tabular-nums">
            {{ stay.startDate }}
        </BaseTableCell>
        <BaseTableCell class="whitespace-nowrap tabular-nums">
            <span
                v-if="isCurrent"
                class="rounded-full bg-success-100 px-2 py-0.5 text-xs font-medium text-success-800 dark:bg-success-950 dark:text-success-300"
            >
                Admitted
            </span>
            <template v-else>{{ stay.endDate }}</template>
        </BaseTableCell>
        <BaseTableCell class="w-0 text-right">
            <BaseTableActionButton
                v-if="isCurrent"
                tone="brand"
                @click.stop="$emit('discharge', stay)"
            >
                <LogOut />
                Discharge
            </BaseTableActionButton>
        </BaseTableCell>
    </BaseTableRow>
</template>
