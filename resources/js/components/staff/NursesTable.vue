<script lang="ts">
import type { PropType } from 'vue'
import { defineComponent } from 'vue'
import BaseTable from '@/components/base/BaseTable.vue'
import NursesTableRow from '@/components/staff/NursesTableRow.vue'
import type { Nurse } from '@/types'

export default defineComponent({
    name: 'NursesTable',

    components: {
        BaseTable,
        NursesTableRow,
    },

    props: {
        nurses: {
            type: Array as PropType<Nurse[]>,
            required: true,
        },
        isLoading: {
            type: Boolean,
            default: false,
        },
        emptyMessage: {
            type: String,
            default: 'No nurses yet.',
        },
    },

    emits: ['edit', 'offboard'],

    data() {
        return {
            columnHeadings: ['Nurse ID', 'Name', 'Position', 'SSN', ''],
        }
    },
})
</script>

<template>
    <BaseTable
        :column-headings="columnHeadings"
        :is-loading="isLoading"
        :is-empty="nurses.length === 0"
        :empty-message="emptyMessage"
    >
        <NursesTableRow
            v-for="nurse in nurses"
            :key="nurse.id"
            :nurse="nurse"
            @edit="$emit('edit', $event)"
            @offboard="$emit('offboard', $event)"
        />
    </BaseTable>
</template>
