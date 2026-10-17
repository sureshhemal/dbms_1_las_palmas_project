<script lang="ts">
import { defineComponent } from 'vue'
import PublicHeader from '@/components/PublicHeader.vue'

export default defineComponent({
    name: 'PublicLayout',

    components: {
        PublicHeader,
    },
})
</script>

<template>
    <div
        class="flex min-h-screen flex-col bg-white text-neutral-900 dark:bg-neutral-950 dark:text-neutral-100"
    >
        <PublicHeader />

        <main class="mx-auto w-full max-w-6xl flex-1 sm:px-2">
            <slot />
        </main>
    </div>
</template>
