<script lang="ts">
import type { PropType } from 'vue'
import { defineComponent } from 'vue'
import BaseTable from '@/components/base/BaseTable.vue'
import PhysiciansTableRow from '@/components/staff/PhysiciansTableRow.vue'
import type { ColumnAlignment, Physician } from '@/types'

export default defineComponent({
    name: 'PhysiciansTable',

    components: {
        BaseTable,
        PhysiciansTableRow,
    },

    props: {
        physicians: {
            type: Array as PropType<Physician[]>,
            required: true,
        },
        isLoading: {
            type: Boolean,
            default: false,
        },
        emptyMessage: {
            type: String,
            default: 'No physicians yet.',
        },
    },

    emits: ['edit', 'offboard'],

    data() {
        return {
            columnHeadings: [
                'Physician ID',
                'Name',
                'Position',
                'Departments',
                'Heads',
                'SSN',
                '',
            ],
            columnAlignments: {
                'Physician ID': 'center',
                SSN: 'center',
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
        :is-empty="physicians.length === 0"
        :empty-message="emptyMessage"
        :column-alignments="columnAlignments"
    >
        <PhysiciansTableRow
            v-for="physician in physicians"
            :key="physician.id"
            :physician="physician"
            @edit="$emit('edit', $event)"
            @offboard="$emit('offboard', $event)"
        />
    </BaseTable>
</template>
