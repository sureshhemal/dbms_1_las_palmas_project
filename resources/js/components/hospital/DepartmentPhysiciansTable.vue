<script lang="ts">
import type { PropType } from 'vue'
import { defineComponent } from 'vue'
import BaseTable from '@/components/base/BaseTable.vue'
import DepartmentPhysiciansTableRow from '@/components/hospital/DepartmentPhysiciansTableRow.vue'
import type { DepartmentPhysician } from '@/types'

export default defineComponent({
    name: 'DepartmentPhysiciansTable',

    components: {
        BaseTable,
        DepartmentPhysiciansTableRow,
    },

    props: {
        physicians: {
            type: Array as PropType<DepartmentPhysician[]>,
            required: true,
        },
        isLoading: {
            type: Boolean,
            default: false,
        },
    },

    data() {
        return {
            columnHeadings: ['Physician ID', 'Name', 'Position', 'Joined'],
        }
    },
})
</script>

<template>
    <BaseTable
        :column-headings="columnHeadings"
        :is-loading="isLoading"
        :is-empty="physicians.length === 0"
        empty-message="No physicians work in this department yet."
    >
        <DepartmentPhysiciansTableRow
            v-for="physician in physicians"
            :key="physician.id"
            :physician="physician"
        />
    </BaseTable>
</template>
