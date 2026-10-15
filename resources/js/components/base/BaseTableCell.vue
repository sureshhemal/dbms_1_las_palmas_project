<script lang="ts">
import { defineComponent } from 'vue'

export default defineComponent({
    name: 'BaseTableCell',
})
</script>

<template>
    <td class="px-4 py-3 whitespace-nowrap">
        <slot />
    </td>
</template>
