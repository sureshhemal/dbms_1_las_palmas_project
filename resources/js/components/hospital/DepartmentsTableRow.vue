<script lang="ts">
import { Pencil } from '@lucide/vue'
import type { PropType } from 'vue'
import { defineComponent } from 'vue'
import BaseTableActionButton from '@/components/base/BaseTableActionButton.vue'
import BaseTableCell from '@/components/base/BaseTableCell.vue'
import BaseTableRow from '@/components/base/BaseTableRow.vue'
import type { Department } from '@/types'

export default defineComponent({
    name: 'DepartmentsTableRow',

    components: {
        BaseTableActionButton,
        BaseTableCell,
        BaseTableRow,
        Pencil,
    },

    props: {
        department: {
            type: Object as PropType<Department>,
            required: true,
        },
    },

    emits: ['edit', 'open'],

    computed: {
        headName(): string {
            return this.department.head?.name ?? '—'
        },
    },
})
</script>

<template>
    <BaseTableRow is-selectable @click="$emit('open', department)">
        <BaseTableCell class="font-mono">
            {{ department.deptID }}
        </BaseTableCell>
        <BaseTableCell class="font-medium">
            {{ department.name }}
        </BaseTableCell>
        <BaseTableCell
            :class="{
                'text-neutral-500 dark:text-neutral-400': !department.head,
            }"
        >
            {{ headName }}
        </BaseTableCell>
        <BaseTableCell class="w-0 text-right">
            <BaseTableActionButton
                tone="brand"
                @click.stop="$emit('edit', department)"
            >
                <Pencil />
                Edit
            </BaseTableActionButton>
        </BaseTableCell>
    </BaseTableRow>
</template>
