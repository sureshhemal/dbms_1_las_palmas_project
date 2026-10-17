<script lang="ts">
import type { PropType } from 'vue'
import { defineComponent } from 'vue'
import BaseTable from '@/components/base/BaseTable.vue'
import TodayDischargesTableRow from '@/components/dashboard/TodayDischargesTableRow.vue'
import type { ColumnAlignment, TodayDischarge } from '@/types'

export default defineComponent({
    name: 'TodayDischargesTable',

    components: {
        BaseTable,
        TodayDischargesTableRow,
    },

    props: {
        discharges: {
            type: Array as PropType<TodayDischarge[]>,
            required: true,
        },
        isLoading: {
            type: Boolean,
            default: false,
        },
    },

    data() {
        return {
            columnHeadings: ['Patient', 'Room', 'Admitted', 'Stay'],
            columnAlignments: {
                Room: 'center',
                Admitted: 'center',
                Stay: 'center',
            } as Record<string, ColumnAlignment>,
        }
    },
})
</script>

<template>
    <BaseTable
        :column-headings="columnHeadings"
        :column-alignments="columnAlignments"
        :is-loading="isLoading"
        :is-empty="discharges.length === 0"
        empty-message="No discharges planned today."
    >
        <TodayDischargesTableRow
            v-for="discharge in discharges"
            :key="discharge.id"
            :discharge="discharge"
        />
    </BaseTable>
</template>
