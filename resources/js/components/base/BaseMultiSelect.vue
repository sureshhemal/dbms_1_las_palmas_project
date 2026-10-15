<script lang="ts">
import { ChevronDown, Search } from '@lucide/vue'
import type { PropType } from 'vue'
import { defineComponent } from 'vue'
import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
import {
    DropdownMenu,
    DropdownMenuCheckboxItem,
    DropdownMenuContent,
    DropdownMenuSeparator,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu'
import type { SelectOption } from '@/types'

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
        SearchIcon: Search,
        UiButton: Button,
        UiInput: Input,
    },

    props: {
        modelValue: {
            type: Array as PropType<string[]>,
            default: () => [],
        },
        options: {
            type: Array as PropType<Array<string | SelectOption>>,
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
        triggerClass: {
            type: String,
            default: 'w-48',
        },
    },

    emits: ['update:modelValue'],

    data() {
        return {
            isOpen: false,
            draftSelection: [...this.modelValue],
            optionSearchTerm: '',
        }
    },

    computed: {
        labelledOptions(): SelectOption[] {
            return this.options.map((option) =>
                typeof option === 'string'
                    ? { value: option, label: option }
                    : option,
            )
        },

        visibleOptions(): SelectOption[] {
            const optionSearchTerm = this.optionSearchTerm.trim().toLowerCase()

            return this.labelledOptions.filter((option) =>
                option.label.toLowerCase().includes(optionSearchTerm),
            )
        },

        selectedLabels(): string[] {
            return this.labelledOptions
                .filter((option) => this.modelValue.includes(option.value))
                .map((option) => option.label)
        },

        selectionSummary(): string {
            if (this.modelValue.length === 0) {
                return this.placeholder
            }

            if (this.modelValue.length === this.options.length) {
                return this.allSelectedLabel
            }

            if (this.modelValue.length <= mostOptionsNamedInSummary) {
                return this.selectedLabels.join(', ')
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
                this.optionSearchTerm = ''
            }

            this.isOpen = isOpen
        },

        focusOptionSearch(event: Event): void {
            event.preventDefault()

            this.$nextTick(() => {
                const optionSearchInput = this.$refs.optionSearchInput as
                    | { $el: HTMLInputElement }
                    | undefined

                optionSearchInput?.$el.focus()
            })
        },

        keepTypingInsideOptionSearch(event: KeyboardEvent): void {
            if (event.key !== 'Escape') {
                event.stopPropagation()
            }
        },

        isInDraft(option: string): boolean {
            return this.draftSelection.includes(option)
        },

        toggleInDraft(option: string): void {
            this.draftSelection = this.isInDraft(option)
                ? this.draftSelection.filter(
                      (draftOption) => draftOption !== option,
                  )
                : this.labelledOptions
                      .map((orderedOption) => orderedOption.value)
                      .filter(
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
                class="flex h-9 items-center justify-between gap-2 rounded-md border border-neutral-200 bg-transparent px-3 text-sm shadow-xs outline-none focus-visible:border-brand-700 focus-visible:ring-3 focus-visible:ring-brand-700/30 dark:border-neutral-800 dark:bg-neutral-950 dark:focus-visible:border-brand-400 dark:focus-visible:ring-brand-400/30"
                :class="triggerClass"
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

        <DropdownMenuContent
            align="start"
            class="w-56"
            @open-auto-focus="focusOptionSearch"
        >
            <div class="relative p-1">
                <SearchIcon
                    class="pointer-events-none absolute top-1/2 left-3 size-3.5 -translate-y-1/2 text-neutral-400"
                />
                <UiInput
                    ref="optionSearchInput"
                    v-model="optionSearchTerm"
                    type="search"
                    placeholder="Search"
                    :aria-label="`Search ${placeholder}`"
                    class="h-8 pl-7"
                    @keydown="keepTypingInsideOptionSearch"
                />
            </div>

            <p
                v-if="visibleOptions.length === 0"
                class="px-2 py-1.5 text-sm text-neutral-500 dark:text-neutral-400"
            >
                No matches
            </p>

            <DropdownMenuCheckboxItem
                v-for="option in visibleOptions"
                :key="option.value"
                :model-value="isInDraft(option.value)"
                @select.prevent="toggleInDraft(option.value)"
            >
                {{ option.label }}
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
