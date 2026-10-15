<script lang="ts">
import { defineComponent } from 'vue'

export default defineComponent({
    name: 'BaseTableRow',

    props: {
        isSelectable: {
            type: Boolean,
            default: false,
        },
    },
})
</script>

<template>
    <tr
        class="text-neutral-900 transition-colors hover:bg-neutral-50 dark:text-neutral-100 dark:hover:bg-neutral-900"
        :class="{ 'cursor-pointer': isSelectable }"
    >
        <slot />
    </tr>
</template>
