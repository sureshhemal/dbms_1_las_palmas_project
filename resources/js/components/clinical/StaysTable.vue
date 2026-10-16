<script lang="ts">
import type { PropType } from 'vue'
import { defineComponent } from 'vue'
import BaseTable from '@/components/base/BaseTable.vue'
import StaysTableRow from '@/components/clinical/StaysTableRow.vue'
import type { Stay } from '@/types'

export default defineComponent({
    name: 'StaysTable',

    components: {
        BaseTable,
        StaysTableRow,
    },

    props: {
        stays: {
            type: Array as PropType<Stay[]>,
            required: true,
        },
        isLoading: {
            type: Boolean,
            default: false,
        },
        emptyMessage: {
            type: String,
            default: 'No stays yet.',
        },
    },

    emits: ['discharge'],

    data() {
        return {
            columnHeadings: [
                'Stay ID',
                'Patient',
                'Room',
                'Admitted',
                'Discharged',
                '',
            ],
        }
    },
})
</script>

<template>
    <BaseTable
        :column-headings="columnHeadings"
        :is-loading="isLoading"
        :is-empty="stays.length === 0"
        :empty-message="emptyMessage"
    >
        <StaysTableRow
            v-for="stay in stays"
            :key="stay.id"
            :stay="stay"
            @discharge="$emit('discharge', $event)"
        />
    </BaseTable>
</template>
