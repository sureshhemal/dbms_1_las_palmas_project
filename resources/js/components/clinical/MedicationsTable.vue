<script lang="ts">
import type { PropType } from 'vue'
import { defineComponent } from 'vue'
import BaseTable from '@/components/base/BaseTable.vue'
import MedicationsTableRow from '@/components/clinical/MedicationsTableRow.vue'
import type { Medication } from '@/types'

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
    },

    emits: ['edit', 'remove'],

    data() {
        return {
            columnHeadings: ['Med ID', 'Name', ''],
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
