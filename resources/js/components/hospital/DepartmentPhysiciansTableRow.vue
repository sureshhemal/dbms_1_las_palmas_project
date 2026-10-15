<script lang="ts">
import type { PropType } from 'vue'
import { defineComponent } from 'vue'
import BaseTableCell from '@/components/base/BaseTableCell.vue'
import BaseTableRow from '@/components/base/BaseTableRow.vue'
import { formatDate } from '@/lib/formatDate'
import type { DepartmentPhysician } from '@/types'

export default defineComponent({
    name: 'DepartmentPhysiciansTableRow',

    components: {
        BaseTableCell,
        BaseTableRow,
    },

    props: {
        physician: {
            type: Object as PropType<DepartmentPhysician>,
            required: true,
        },
    },

    computed: {
        joinDate(): string {
            return this.physician.joined_at
                ? formatDate(this.physician.joined_at)
                : '—'
        },
    },
})
</script>

<template>
    <BaseTableRow>
        <BaseTableCell class="font-mono">
            {{ physician.physicianID }}
        </BaseTableCell>
        <BaseTableCell class="font-medium">
            {{ physician.name }}
        </BaseTableCell>
        <BaseTableCell>{{ physician.position }}</BaseTableCell>
        <BaseTableCell>{{ joinDate }}</BaseTableCell>
    </BaseTableRow>
</template>
