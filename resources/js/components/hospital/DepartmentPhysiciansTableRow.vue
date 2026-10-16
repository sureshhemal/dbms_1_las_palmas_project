<script lang="ts">
import type { PropType } from 'vue'
import { defineComponent } from 'vue'
import BaseTableCell from '@/components/base/BaseTableCell.vue'
import BaseTableRow from '@/components/base/BaseTableRow.vue'
import PhysicianNameLink from '@/components/staff/PhysicianNameLink.vue'
import { formatDate } from '@/lib/formatDate'
import type { DepartmentPhysician } from '@/types'

export default defineComponent({
    name: 'DepartmentPhysiciansTableRow',

    components: {
        BaseTableCell,
        BaseTableRow,
        PhysicianNameLink,
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
        <BaseTableCell align="center" class="font-mono">
            {{ physician.physicianID }}
        </BaseTableCell>
        <BaseTableCell>
            <PhysicianNameLink :physician="physician" />
        </BaseTableCell>
        <BaseTableCell>{{ physician.position }}</BaseTableCell>
        <BaseTableCell align="center">{{ joinDate }}</BaseTableCell>
    </BaseTableRow>
</template>
