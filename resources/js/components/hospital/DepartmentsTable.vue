<script lang="ts">
import type { PropType } from 'vue'
import { defineComponent } from 'vue'
import BaseTable from '@/components/base/BaseTable.vue'
import DepartmentsTableRow from '@/components/hospital/DepartmentsTableRow.vue'
import type { ColumnAlignment, Department } from '@/types'

export default defineComponent({
    name: 'DepartmentsTable',

    components: {
        BaseTable,
        DepartmentsTableRow,
    },

    props: {
        departments: {
            type: Array as PropType<Department[]>,
            required: true,
        },
        isLoading: {
            type: Boolean,
            default: false,
        },
    },

    emits: ['edit', 'open'],

    data() {
        return {
            columnHeadings: ['Department ID', 'Name', 'Head', ''],
            columnAlignments: {
                'Department ID': 'center',
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
        :is-empty="departments.length === 0"
        empty-message="No departments."
        :column-alignments="columnAlignments"
    >
        <DepartmentsTableRow
            v-for="department in departments"
            :key="department.id"
            :department="department"
            @edit="$emit('edit', $event)"
            @open="$emit('open', $event)"
        />
    </BaseTable>
</template>
