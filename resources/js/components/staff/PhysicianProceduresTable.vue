<script lang="ts">
import type { PropType } from 'vue'
import { defineComponent } from 'vue'
import BaseTable from '@/components/base/BaseTable.vue'
import PhysicianProceduresTableRow from '@/components/staff/PhysicianProceduresTableRow.vue'
import type { ColumnAlignment, PhysicianPerformedProcedure } from '@/types'

export default defineComponent({
    name: 'PhysicianProceduresTable',

    components: {
        BaseTable,
        PhysicianProceduresTableRow,
    },

    props: {
        performedProcedures: {
            type: Array as PropType<PhysicianPerformedProcedure[]>,
            required: true,
        },
        isLoading: {
            type: Boolean,
            default: false,
        },
        emptyMessage: {
            type: String,
            required: true,
        },
    },

    data() {
        return {
            columnHeadings: ['Date', 'Patient', 'Procedure', 'Room', 'Nurse'],
            columnAlignments: {
                Date: 'center',
                Room: 'center',
            } as Record<string, ColumnAlignment>,
        }
    },
})
</script>

<template>
    <BaseTable
        :column-headings="columnHeadings"
        :is-loading="isLoading"
        :is-empty="performedProcedures.length === 0"
        :empty-message="emptyMessage"
        :column-alignments="columnAlignments"
    >
        <PhysicianProceduresTableRow
            v-for="performedProcedure in performedProcedures"
            :key="performedProcedure.id"
            :performed-procedure="performedProcedure"
        />
    </BaseTable>
</template>
