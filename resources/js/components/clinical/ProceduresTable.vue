<script lang="ts">
import type { PropType } from 'vue'
import { defineComponent } from 'vue'
import BaseTable from '@/components/base/BaseTable.vue'
import ProceduresTableRow from '@/components/clinical/ProceduresTableRow.vue'
import type {
    ColumnAlignment,
    Procedure,
    SortDirection,
    TableSorting,
} from '@/types'

export default defineComponent({
    name: 'ProceduresTable',

    components: {
        BaseTable,
        ProceduresTableRow,
    },

    props: {
        procedures: {
            type: Array as PropType<Procedure[]>,
            required: true,
        },
        isLoading: {
            type: Boolean,
            default: false,
        },
        emptyMessage: {
            type: String,
            default: 'No procedures yet.',
        },
        sorting: {
            type: Object as PropType<TableSorting>,
            required: true,
        },
    },

    emits: ['edit', 'remove', 'sort'],

    data() {
        return {
            columnHeadings: [
                'Procedure ID',
                'Name',
                'Share of Performed Procedures',
                'Cost',
                '',
            ],
            sortableColumns: {
                Name: 'name',
                'Share of Performed Procedures': 'performedShare',
            },
            firstSortDirections: {
                performedShare: 'desc',
            } as Record<string, SortDirection>,
            columnAlignments: {
                'Procedure ID': 'center',
                'Share of Performed Procedures': 'center',
                Cost: 'right',
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
        :is-empty="procedures.length === 0"
        :empty-message="emptyMessage"
        :sortable-columns="sortableColumns"
        :sorting="sorting"
        :first-sort-directions="firstSortDirections"
        :column-alignments="columnAlignments"
        @sort="$emit('sort', $event)"
    >
        <ProceduresTableRow
            v-for="procedure in procedures"
            :key="procedure.id"
            :procedure="procedure"
            @edit="$emit('edit', $event)"
            @remove="$emit('remove', $event)"
        />
    </BaseTable>
</template>
