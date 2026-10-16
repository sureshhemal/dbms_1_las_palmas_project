<script lang="ts">
import type { PropType } from 'vue'
import { defineComponent } from 'vue'
import type { ColumnAlignment } from '@/types'

export default defineComponent({
    name: 'BaseTableCell',

    props: {
        align: {
            type: String as PropType<ColumnAlignment>,
            default: 'left',
        },
    },

    computed: {
        alignmentClass(): string {
            return {
                left: 'text-left',
                center: 'text-center',
                right: 'text-right',
            }[this.align]
        },
    },
})
</script>

<template>
    <td class="px-4 py-3 whitespace-nowrap" :class="alignmentClass">
        <slot />
    </td>
</template>
