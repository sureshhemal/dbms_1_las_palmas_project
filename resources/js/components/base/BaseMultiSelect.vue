<script lang="ts">
import { ChevronDown } from '@lucide/vue'
import type { PropType } from 'vue'
import { defineComponent } from 'vue'
import { Button } from '@/components/ui/button'
import {
    DropdownMenu,
    DropdownMenuCheckboxItem,
    DropdownMenuContent,
    DropdownMenuSeparator,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu'

const mostOptionsNamedInSummary = 2

export default defineComponent({
    name: 'BaseMultiSelect',

    components: {
        ChevronDown,
        DropdownMenu,
        DropdownMenuCheckboxItem,
        DropdownMenuContent,
        DropdownMenuSeparator,
        DropdownMenuTrigger,
        UiButton: Button,
    },

    props: {
        modelValue: {
            type: Array as PropType<string[]>,
            default: () => [],
        },
        options: {
            type: Array as PropType<string[]>,
            required: true,
        },
        placeholder: {
            type: String,
            required: true,
        },
        allSelectedLabel: {
            type: String,
            default: 'All',
        },
    },

    emits: ['update:modelValue'],

    data() {
        return {
            isOpen: false,
            draftSelection: [...this.modelValue],
        }
    },

    computed: {
        selectionSummary(): string {
            if (this.modelValue.length === 0) {
                return this.placeholder
            }

            if (this.modelValue.length === this.options.length) {
                return this.allSelectedLabel
            }

            if (this.modelValue.length <= mostOptionsNamedInSummary) {
                return this.modelValue.join(', ')
            }

            return `${this.modelValue.length} selected`
        },

        draftDiffersFromSelection(): boolean {
            return (
                this.draftSelection.length !== this.modelValue.length ||
                this.draftSelection.some(
                    (option) => !this.modelValue.includes(option),
                )
            )
        },
    },

    methods: {
        changeOpenState(isOpen: boolean): void {
            if (isOpen) {
                this.draftSelection = [...this.modelValue]
            }

            this.isOpen = isOpen
        },

        isInDraft(option: string): boolean {
            return this.draftSelection.includes(option)
        },

        toggleInDraft(option: string): void {
            this.draftSelection = this.isInDraft(option)
                ? this.draftSelection.filter(
                      (draftOption) => draftOption !== option,
                  )
                : this.options.filter(
                      (orderedOption) =>
                          orderedOption === option ||
                          this.isInDraft(orderedOption),
                  )
        },

        applyDraft(): void {
            if (this.draftDiffersFromSelection) {
                this.$emit('update:modelValue', [...this.draftSelection])
            }

            this.isOpen = false
        },
    },
})
</script>

<template>
    <DropdownMenu :open="isOpen" @update:open="changeOpenState">
        <DropdownMenuTrigger as-child>
            <button
                type="button"
                class="flex h-9 w-48 items-center justify-between gap-2 rounded-md border border-neutral-200 bg-transparent px-3 text-sm shadow-xs outline-none focus-visible:border-brand-700 focus-visible:ring-3 focus-visible:ring-brand-700/30 dark:border-neutral-800 dark:bg-neutral-950 dark:focus-visible:border-brand-400 dark:focus-visible:ring-brand-400/30"
                :aria-label="placeholder"
            >
                <span
                    class="truncate"
                    :class="{
                        'text-neutral-500 dark:text-neutral-400':
                            modelValue.length === 0,
                    }"
                >
                    {{ selectionSummary }}
                </span>
                <ChevronDown class="size-4 shrink-0 opacity-50" />
            </button>
        </DropdownMenuTrigger>

        <DropdownMenuContent align="start" class="w-48">
            <DropdownMenuCheckboxItem
                v-for="option in options"
                :key="option"
                :model-value="isInDraft(option)"
                @select.prevent="toggleInDraft(option)"
            >
                {{ option }}
            </DropdownMenuCheckboxItem>

            <DropdownMenuSeparator />

            <div class="p-1">
                <UiButton size="sm" class="w-full" @click="applyDraft">
                    Apply
                </UiButton>
            </div>
        </DropdownMenuContent>
    </DropdownMenu>
</template>
