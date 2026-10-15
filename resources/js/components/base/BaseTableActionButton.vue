<script lang="ts">
import type { PropType } from 'vue'
import { defineComponent } from 'vue'

const toneClasses = {
    brand: 'text-brand-700 hover:bg-brand-50 dark:text-brand-400 dark:hover:bg-brand-950',
    danger: 'text-danger-600 hover:bg-danger-50 dark:text-danger-400 dark:hover:bg-danger-950',
}

export default defineComponent({
    name: 'BaseTableActionButton',

    props: {
        tone: {
            type: String as PropType<keyof typeof toneClasses>,
            default: 'brand',
        },
    },

    computed: {
        toneClass(): string {
            return toneClasses[this.tone]
        },

        isBusy(): boolean {
            return this.$wait.any() > 0
        },
    },
})
</script>

<template>
    <button
        type="button"
        class="inline-flex items-center gap-1.5 rounded-md px-2 py-1 text-xs font-medium disabled:pointer-events-none disabled:opacity-50 [&_svg]:size-3.5"
        :class="toneClass"
        :disabled="isBusy"
    >
        <slot />
    </button>
</template>
