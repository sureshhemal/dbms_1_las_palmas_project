<script lang="ts">
import type { PropType } from 'vue'
import { defineComponent } from 'vue'

export default defineComponent({
    name: 'PatientSummaryCard',

    props: {
        label: {
            type: String,
            required: true,
        },
        value: {
            type: String,
            default: '',
        },
        detail: {
            type: String,
            default: '',
        },
        tone: {
            type: String as PropType<'brand' | 'success' | 'warning' | 'info'>,
            required: true,
        },
        isMuted: {
            type: Boolean,
            default: false,
        },
        isLoading: {
            type: Boolean,
            default: false,
        },
    },

    computed: {
        iconClasses(): string {
            return {
                brand: 'bg-brand-100 text-brand-700 dark:bg-brand-950 dark:text-brand-300',
                success:
                    'bg-success-100 text-success-700 dark:bg-success-950 dark:text-success-300',
                warning:
                    'bg-warning-100 text-warning-700 dark:bg-warning-950 dark:text-warning-300',
                info: 'bg-info-100 text-info-700 dark:bg-info-950 dark:text-info-300',
            }[this.tone]
        },
    },
})
</script>

<template>
    <section
        class="flex items-start gap-3 rounded-xl border border-neutral-200 bg-white p-4 dark:border-neutral-800 dark:bg-neutral-950"
    >
        <div
            class="flex size-10 shrink-0 items-center justify-center rounded-lg [&>svg]:size-5"
            :class="iconClasses"
        >
            <slot name="icon" />
        </div>

        <div class="min-w-0 flex-1">
            <p class="text-xs text-neutral-500 dark:text-neutral-400">
                {{ label }}
            </p>

            <div v-if="isLoading" class="mt-2 space-y-2">
                <div
                    class="h-5 w-2/3 animate-pulse rounded bg-neutral-200 dark:bg-neutral-800"
                />
                <div
                    class="h-3 w-1/3 animate-pulse rounded bg-neutral-200 dark:bg-neutral-800"
                />
            </div>

            <template v-else>
                <p
                    class="mt-1 truncate text-lg font-semibold tabular-nums"
                    :class="
                        isMuted
                            ? 'text-neutral-500 dark:text-neutral-400'
                            : 'text-neutral-900 dark:text-neutral-100'
                    "
                    :title="value"
                >
                    {{ value }}
                </p>
                <p
                    v-if="detail"
                    class="text-sm text-neutral-500 tabular-nums dark:text-neutral-400"
                >
                    {{ detail }}
                </p>
            </template>
        </div>
    </section>
</template>
