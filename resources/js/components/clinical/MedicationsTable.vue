<script lang="ts">
import type { PropType } from 'vue'
import { defineComponent } from 'vue'
import BaseTable from '@/components/base/BaseTable.vue'
import MedicationsTableRow from '@/components/clinical/MedicationsTableRow.vue'
import type {
    ColumnAlignment,
    Medication,
    SortDirection,
    TableSorting,
} from '@/types'

export default defineComponent({
    name: 'MedicationsTable',

    components: {
        BaseTable,
        MedicationsTableRow,
    },

    props: {
        medications: {
            type: Array as PropType<Medication[]>,
            required: true,
        },
        isLoading: {
            type: Boolean,
            default: false,
        },
        emptyMessage: {
            type: String,
            default: 'No medications yet.',
        },
        sorting: {
            type: Object as PropType<TableSorting>,
            required: true,
        },
    },

    emits: ['edit', 'remove', 'sort'],

    data() {
        return {
            columnHeadings: ['Med ID', 'Name', 'Share of Prescriptions', ''],
            sortableColumns: {
                Name: 'name',
                'Share of Prescriptions': 'prescriptionShare',
            },
            firstSortDirections: {
                prescriptionShare: 'desc',
            } as Record<string, SortDirection>,
            columnAlignments: {
                'Med ID': 'center',
                'Share of Prescriptions': 'center',
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
        :is-empty="medications.length === 0"
        :empty-message="emptyMessage"
        :sortable-columns="sortableColumns"
        :sorting="sorting"
        :first-sort-directions="firstSortDirections"
        :column-alignments="columnAlignments"
        @sort="$emit('sort', $event)"
    >
        <MedicationsTableRow
            v-for="medication in medications"
            :key="medication.id"
            :medication="medication"
            @edit="$emit('edit', $event)"
            @remove="$emit('remove', $event)"
        />
    </BaseTable>
</template>
