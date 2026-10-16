<script lang="ts">
import type { PropType } from 'vue'
import { defineComponent } from 'vue'
import BaseTable from '@/components/base/BaseTable.vue'
import RoomsTableRow from '@/components/hospital/RoomsTableRow.vue'
import type { ColumnAlignment, Room } from '@/types'

export default defineComponent({
    name: 'RoomsTable',

    components: {
        BaseTable,
        RoomsTableRow,
    },

    props: {
        rooms: {
            type: Array as PropType<Room[]>,
            required: true,
        },
        isLoading: {
            type: Boolean,
            default: false,
        },
        emptyMessage: {
            type: String,
            default: 'No rooms yet.',
        },
    },

    emits: ['edit', 'decommission'],

    data() {
        return {
            columnHeadings: [
                'Room ID',
                'Room Type',
                'Availability',
                'Patients',
                '',
            ],
            columnAlignments: {
                'Room ID': 'center',
                Availability: 'center',
                '': 'right',
            } as Record<string, ColumnAlignment>,
        }
    },
})
</script>

<template>
    <BaseTable
        :column-headings="columnHeadings"
        :is-loading="isLoading"
        :is-empty="rooms.length === 0"
        :empty-message="emptyMessage"
        :column-alignments="columnAlignments"
    >
        <RoomsTableRow
            v-for="room in rooms"
            :key="room.id"
            :room="room"
            @edit="$emit('edit', $event)"
            @decommission="$emit('decommission', $event)"
        />
    </BaseTable>
</template>
