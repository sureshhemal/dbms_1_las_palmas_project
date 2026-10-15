<script lang="ts">
import { UserRound } from '@lucide/vue'
import type { PropType } from 'vue'
import { defineComponent } from 'vue'
import type { Physician } from '@/types'

export default defineComponent({
    name: 'DepartmentHeadCard',

    components: {
        UserRound,
    },

    props: {
        head: {
            type: Object as PropType<Physician | null>,
            default: null,
        },
        isLoading: {
            type: Boolean,
            default: false,
        },
    },
})
</script>

<template>
    <section
        class="rounded-xl border border-neutral-200 bg-white p-4 dark:border-neutral-800 dark:bg-neutral-950"
    >
        <h3
            class="text-xs font-medium tracking-wide text-neutral-500 uppercase dark:text-neutral-400"
        >
            Department Head
        </h3>

        <div v-if="isLoading" class="mt-3 flex items-center gap-3">
            <div
                class="size-10 animate-pulse rounded-full bg-neutral-200 dark:bg-neutral-800"
            />
            <div class="flex-1 space-y-2">
                <div
                    class="h-4 w-1/3 animate-pulse rounded bg-neutral-200 dark:bg-neutral-800"
                />
                <div
                    class="h-3 w-1/4 animate-pulse rounded bg-neutral-200 dark:bg-neutral-800"
                />
            </div>
        </div>

        <div v-else-if="head" class="mt-3 flex items-center gap-3">
            <div
                class="flex size-10 items-center justify-center rounded-full bg-brand-100 text-brand-700 dark:bg-brand-950 dark:text-brand-300"
            >
                <UserRound class="size-5" />
            </div>
            <div>
                <p class="font-medium text-neutral-900 dark:text-neutral-100">
                    {{ head.name }}
                </p>
                <p class="text-sm text-neutral-500 dark:text-neutral-400">
                    {{ head.position }} ·
                    <span class="font-mono">{{ head.physicianID }}</span>
                </p>
            </div>
        </div>

        <p v-else class="mt-3 text-sm text-neutral-500 dark:text-neutral-400">
            No head assigned.
        </p>
    </section>
</template>
