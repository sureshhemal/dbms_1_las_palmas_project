<script lang="ts">
import type { PropType } from 'vue'
import { Pencil, UserMinus } from '@lucide/vue'
import { defineComponent } from 'vue'
import BaseTableActionButton from '@/components/base/BaseTableActionButton.vue'
import BaseTableCell from '@/components/base/BaseTableCell.vue'
import BaseTableRow from '@/components/base/BaseTableRow.vue'
import PhysicianNameLink from '@/components/staff/PhysicianNameLink.vue'
import { maskSsn } from '@/lib/maskSsn'
import type { Physician } from '@/types'

export default defineComponent({
    name: 'PhysiciansTableRow',

    components: {
        BaseTableActionButton,
        Pencil,
        UserMinus,
        BaseTableCell,
        BaseTableRow,
        PhysicianNameLink,
    },

    props: {
        physician: {
            type: Object as PropType<Physician>,
            required: true,
        },
    },

    emits: ['edit', 'offboard'],

    computed: {
        departmentsSummary(): string {
            const departments = this.physician.departments ?? []

            if (departments.length === 0) {
                return '—'
            }

            return departments.map((department) => department.name).join(', ')
        },

        worksInAnyDepartment(): boolean {
            return (this.physician.departments ?? []).length > 0
        },

        headedDepartmentsSummary(): string {
            const headedDepartments = this.physician.headed_departments ?? []

            if (headedDepartments.length === 0) {
                return '—'
            }

            return headedDepartments
                .map(
                    (department) => `${department.name} (${department.deptID})`,
                )
                .join(', ')
        },

        headsAnyDepartment(): boolean {
            return (this.physician.headed_departments ?? []).length > 0
        },

        maskedSsn(): string {
            return this.physician.ssn ? maskSsn(this.physician.ssn) : '—'
        },
    },
})
</script>

<template>
    <BaseTableRow>
        <BaseTableCell class="font-mono">
            {{ physician.physicianID }}
        </BaseTableCell>
        <BaseTableCell>
            <PhysicianNameLink :physician="physician" />
        </BaseTableCell>
        <BaseTableCell>{{ physician.position }}</BaseTableCell>
        <BaseTableCell
            :class="{
                'text-neutral-500 dark:text-neutral-400': !worksInAnyDepartment,
            }"
        >
            {{ departmentsSummary }}
        </BaseTableCell>
        <BaseTableCell
            :class="{
                'text-neutral-500 dark:text-neutral-400': !headsAnyDepartment,
            }"
        >
            {{ headedDepartmentsSummary }}
        </BaseTableCell>
        <BaseTableCell class="font-mono text-neutral-500 dark:text-neutral-400">
            {{ maskedSsn }}
        </BaseTableCell>
        <BaseTableCell class="w-0 text-right">
            <div class="flex items-center justify-end gap-1">
                <BaseTableActionButton
                    tone="brand"
                    @click.stop="$emit('edit', physician)"
                >
                    <Pencil />
                    Edit
                </BaseTableActionButton>
                <BaseTableActionButton
                    tone="danger"
                    @click.stop="$emit('offboard', physician)"
                >
                    <UserMinus />
                    Offboard
                </BaseTableActionButton>
            </div>
        </BaseTableCell>
    </BaseTableRow>
</template>
