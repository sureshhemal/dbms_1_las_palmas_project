<script setup lang="ts">
import type { HTMLAttributes } from 'vue'

defineOptions({
    inheritAttrs: false,
})

type Props = {
    className?: HTMLAttributes['class']
}

defineProps<Props>()
</script>

<template>
    <svg
        xmlns="http://www.w3.org/2000/svg"
        viewBox="0 0 24 24"
        :class="className"
        v-bind="$attrs"
    >
        <path
            fill="currentColor"
            d="M9.75 2.5h4.5a1 1 0 0 1 1 1v5.25h5.25a1 1 0 0 1 1 1v4.5a1 1 0 0 1-1 1h-5.25v5.25a1 1 0 0 1-1 1h-4.5a1 1 0 0 1-1-1v-5.25H3.5a1 1 0 0 1-1-1v-4.5a1 1 0 0 1 1-1h5.25V3.5a1 1 0 0 1 1-1Z"
        />
    </svg>
</template>
