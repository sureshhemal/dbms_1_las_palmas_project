<script lang="ts">
import type { PropType } from 'vue'
import { defineComponent } from 'vue'
import BaseTable from '@/components/base/BaseTable.vue'
import ProceduresTableRow from '@/components/clinical/ProceduresTableRow.vue'
import type { Procedure } from '@/types'

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
    },

    emits: ['edit', 'remove'],

    data() {
        return {
            columnHeadings: ['Procedure ID', 'Name', 'Cost', ''],
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
