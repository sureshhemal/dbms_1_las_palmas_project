<script lang="ts">
import { DoorClosed, Pencil } from '@lucide/vue'
import type { PropType } from 'vue'
import { defineComponent } from 'vue'
import BaseTableActionButton from '@/components/base/BaseTableActionButton.vue'
import BaseTableCell from '@/components/base/BaseTableCell.vue'
import BaseTableRow from '@/components/base/BaseTableRow.vue'
import PatientNameLink from '@/components/patient/PatientNameLink.vue'
import type { Room, RoomOccupant } from '@/types'

export default defineComponent({
    name: 'RoomsTableRow',

    components: {
        BaseTableActionButton,
        BaseTableCell,
        BaseTableRow,
        DoorClosed,
        PatientNameLink,
        Pencil,
    },

    props: {
        room: {
            type: Object as PropType<Room>,
            required: true,
        },
    },

    emits: ['edit', 'decommission'],

    computed: {
        occupants(): RoomOccupant[] {
            return this.room.current_stays ?? []
        },

        isFull(): boolean {
            return this.room.bedsFree === 0
        },

        availability(): string {
            if (this.isFull) {
                return 'Full'
            }

            return this.room.bedsFree === 1
                ? '1 bed free'
                : `${this.room.bedsFree} beds free`
        },
    },
})
</script>

<template>
    <BaseTableRow>
        <BaseTableCell class="font-mono">
            {{ room.roomID }}
        </BaseTableCell>
        <BaseTableCell>{{ room.roomType }}</BaseTableCell>
        <BaseTableCell>
            <span
                class="rounded-full px-2 py-0.5 text-xs font-medium"
                :class="
                    isFull
                        ? 'bg-danger-100 text-danger-800 dark:bg-danger-950 dark:text-danger-300'
                        : 'bg-success-100 text-success-800 dark:bg-success-950 dark:text-success-300'
                "
            >
                {{ availability }}
            </span>
        </BaseTableCell>
        <BaseTableCell>
            <span
                v-if="occupants.length === 0"
                class="text-neutral-500 dark:text-neutral-400"
            >
                —
            </span>
            <ul v-else class="space-y-0.5">
                <li v-for="occupant in occupants" :key="occupant.id">
                    <PatientNameLink :patient="occupant.patient" />
                </li>
            </ul>
        </BaseTableCell>
        <BaseTableCell class="w-0 text-right">
            <div class="flex items-center justify-end gap-1">
                <BaseTableActionButton
                    tone="brand"
                    @click.stop="$emit('edit', room)"
                >
                    <Pencil />
                    Edit
                </BaseTableActionButton>
                <BaseTableActionButton
                    tone="danger"
                    @click.stop="$emit('decommission', room)"
                >
                    <DoorClosed />
                    Decommission
                </BaseTableActionButton>
            </div>
        </BaseTableCell>
    </BaseTableRow>
</template>
