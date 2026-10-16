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
        today: {
            type: String,
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

    emits: ['add-procedure', 'extend', 'discharge'],

    data() {
        return {
            columnHeadings: [
                'Stay ID',
                'Patient',
                'Room',
                'Admitted',
                'Discharge',
                'Status',
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
            :today="today"
            @add-procedure="$emit('add-procedure', $event)"
            @extend="$emit('extend', $event)"
            @discharge="$emit('discharge', $event)"
        />
    </BaseTable>
</template>
